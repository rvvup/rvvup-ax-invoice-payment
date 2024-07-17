<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Process;

use Laminas\Http\Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Rvvup\AxInvoicePayment\Api\GetInvoiceInterface;
use Rvvup\AxInvoicePayment\Sdk\Curl;

class Pay implements HttpPostActionInterface
{
    public const PAYMENT_RVVUP_AX_INTEGRATION = 'payment/rvvup_ax_integration';
    /** @var LoggerInterface */
    private $logger;

    /** @var ScopeConfigInterface */
    private $config;

    /** @var Curl */
    private $curl;

    /** @var SerializerInterface */
    private $json;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var Json */
    private $serializer;

    /** @var GetInvoiceInterface */
    private $getInvoice;

    /**
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param SerializerInterface $json
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param EncryptorInterface $encryptor
     * @param Json $serializer
     * @param GetInvoiceInterface $getInvoice
     */
    public function __construct(
        ScopeConfigInterface $config,
        LoggerInterface      $logger,
        Curl                 $curl,
        SerializerInterface  $json,
        RequestInterface     $request,
        ResultFactory        $resultFactory,
        EncryptorInterface   $encryptor,
        Json                 $serializer,
        GetInvoiceInterface           $getInvoice
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->curl = $curl;
        $this->json = $json;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
        $this->getInvoice = $getInvoice;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $statementId = $this->request->getParam('statement_id');
        $companyId = $this->request->getParam('company_id');
        $accountNumber = $this->request->getParam('account_number');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $url = $this->createCheckout(
                $statementId,
                $companyId,
                $accountNumber
            );
            $result->setData([
                'url' => $url,
                'success' => true
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Rvvup checkout', [$e->getMessage()]);
            $result->setData([
                'success' => false
            ]);
        }
        return $result;
    }

    /**
     * @param string $statementId
     * @param string $companyId
     * @param string $accountId
     * @return string
     * @throws InputException
     */
    private function createCheckout(
        string $statementId,
        string $companyId,
        string $accountId
    ): string
    {
        $params = $this->buildRequestData($statementId, $companyId, $accountId);
        $request = $this->curl->request(
            Request::METHOD_POST,
            $this->getApiUrl($companyId),
            $params
        );
        $body = $this->json->unserialize($request->body);
        if (isset($body['url'])) {
            return $body['url'];
        };
        throw new InputException(__('Missing returnUrl from Rvvup'));
    }

    /**
     * @param string $statementId
     * @param string $companyId
     * @param string $accountId
     * @return array
     */
    private function buildRequestData(
        string $statementId,
        string $companyId,
        string $accountId
    ): array
    {
        $invoices = $this->getInvoice->getListOfInvoicesById($statementId)->getInvoices();
        $invoiceArray = array_map(function($invoice) {
            $data = $invoice->toArray();
            $data['total'] = $data['total']->toArray();
            $data['amountRemaining'] = $data['amountRemaining']->toArray();
            $data['amountPaid'] = $data['amountPaid']->toArray();
            return $data;
        }, $invoices);

        $postData = [
            "connection" => [
                "type" => "MAGENTO_PROXY",
                "statementId" => $statementId,
                "companyId" => $companyId,
                "accountId" => $accountId
            ],
            "invoices" => $invoiceArray
        ];
        $headers = $this->getHeaders($companyId);

        return [
            'headers' => $headers,
            'json' => $postData
        ];
    }

    /**
     * @param string $companyId
     * @return string[]
     */
    private function getHeaders(string $companyId): array
    {
        $token = $this->getAuthToken($companyId);
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ];
    }

    /** @param string $storeId
     * @return string
     * @todo move to rest api sdk
     */
    private function getApiUrl(string $companyId): string
    {
        $merchantId = $this->getMerchantId($companyId);
        $baseUrl = $this->getEndpoint($companyId);
        return str_replace('graphql', "api/2024-03-01/$merchantId/accounts/statements", $baseUrl);
    }

    /**
     * @param string $companyId
     * @param bool $encrypted
     * @return array
     * @throws InputException
     */
    private function getJwt(string $companyId, bool $encrypted = true): array
    {
        $value = $this->config->getValue(self::PAYMENT_RVVUP_AX_INTEGRATION . '/company_jwt_mapping');
        $value = $this->serializer->unserialize($value);
        foreach ($value as $entry) {
            if ($entry['company'] === $companyId) {
                if ($encrypted) {
                    $jwt = $this->encryptor->decrypt($entry['jwt_key']);
                    $parts = explode('.', $jwt);
                    list($head, $body, $crypto) = $parts;
                    return $this->serializer->unserialize(base64_decode($body));
                } else {
                    return ['jwt' => $this->encryptor->decrypt($entry['jwt_key'])];
                }
            }
        }
        throw new InputException(__('There is no saved company named ' . $companyId));
    }

    /**
     * @param string $companyId
     * @return string
     * @throws InputException
     */
    private function getAuthToken(string $companyId): string
    {
        $jwt = $this->getJwt($companyId, false);
        return $jwt['jwt'];
    }

    /**
     * @param string $companyId
     * @return string
     */
    private function getMerchantId(string $companyId): string
    {
        $jwt = $this->getJwt($companyId);
        return (string) $jwt['merchantId'];
    }

    /**
     * @param string $companyId
     * @return string
     */
    private function getEndpoint(string $companyId): string
    {
        $jwt = $this->getJwt($companyId);
        return (string) $jwt['aud'];
    }
}
