<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Process;

use DateTime;
use Laminas\Http\Request;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Rvvup\AxInvoicePayment\Model\UserAgentBuilder;
use Rvvup\AxInvoicePayment\Sdk\Curl;

class Rvvalytix implements HttpPostActionInterface
{
    public const PAYMENT_RVVUP_AX_INTEGRATION = 'payment/rvvup_ax_integration';
    /** @var LoggerInterface */
    private $logger;

    /** @var ScopeConfigInterface */
    private $config;

    /** @var Curl */
    private $curl;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var Json */
    private $serializer;

    /** @var UserAgentBuilder */
    private $userAgentBuilder;

    /**
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param EncryptorInterface $encryptor
     * @param Json $serializer
     * @param UserAgentBuilder $userAgentBuilder
     */
    public function __construct(
        ScopeConfigInterface $config,
        LoggerInterface      $logger,
        Curl                 $curl,
        RequestInterface     $request,
        ResultFactory        $resultFactory,
        EncryptorInterface   $encryptor,
        Json                 $serializer,
        UserAgentBuilder     $userAgentBuilder
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->curl = $curl;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
        $this->userAgentBuilder = $userAgentBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $companyId = $this->request->getParam('company_id');
        $accountNumber = $this->request->getParam('account_number');
        $invoiceId = $this->request->getParam('invoice_id');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $date = (new DateTime('now'))->format(DateTime::ATOM);

        try {
            $type = $this->mapType($this->request->getParam('type'));
            $this->sendEvent(
                $companyId,
                $accountNumber,
                $invoiceId,
                $type,
                $date
            );
            $result->setData([
                'success' => true
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send Rvvup event', [$e->getMessage()]);
            $result->setData([
                'success' => false
            ]);
        }
        return $result;
    }

    /**
     * @param string $companyId
     * @param string $accountId
     * @param string $invoiceId
     * @param string $type
     * @param string $date
     * @throws InputException
     */
    private function sendEvent(
        string $companyId,
        string $accountId,
        string $invoiceId,
        string $type,
        string $date
    )
    {
        $params = $this->buildRequestData($companyId, $accountId, $invoiceId, $type, $date);
        $this->curl->request(
            Request::METHOD_POST,
            $this->getApiUrl($companyId),
            $params
        );
    }

    /**
     * @param string $companyId
     * @param string $accountId
     * @param string $invoiceId
     * @param string $type
     * @param string $date
     * @return array
     * @throws InputException
     */
    private function buildRequestData(
        string $companyId,
        string $accountId,
        string $invoiceId,
        string $type,
        string $date
    ): array
    {
        $postData = [
            "events" => [
                [
                    "merchantId" => $this->getMerchantId($companyId),
                    "name" => $type,
                    "data" => [
                        "companyId" => $companyId,
                        "accountId" => $accountId,
                        "invoiceId" => $invoiceId
                    ],
                    "originCreatedAt" => $date,
                ]
            ],
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
     * @throws InputException
     */
    private function getHeaders(string $companyId): array
    {
        $token = $this->getAuthToken($companyId);
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: ' . $this->userAgentBuilder->get(),
            'Authorization: Bearer ' . $token,
        ];
    }

    /**
     * @param string $type
     * @return string
     * @throws InputException
     */
    private function mapType(string $type): string
    {
        switch ($type) {
            case 'landing':
                return "ACCOUNT_STATEMENT_PLUGIN_PAGE_RENDERED";
            case 'pay_clicked':
                return "ACCOUNT_STATEMENT_PLUGIN_PAGE_PAY_CLICKED";
        }
        throw new InputException(__('Invalid type ' . $type));
    }

    /**
     * @param string $companyId
     * @return string
     * @throws InputException @todo move to rest api sdk
     */
    private function getApiUrl(string $companyId): string
    {
        $baseUrl = $this->getEndpoint($companyId);
        return str_replace('graphql', "rvvalytix", $baseUrl);
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
                    $jwt = $this->encryptor->decrypt($entry['api_key']);
                    $parts = explode('.', $jwt);
                    list($head, $body, $crypto) = $parts;
                    return $this->serializer->unserialize(base64_decode($body));
                } else {
                    return ['jwt' => $this->encryptor->decrypt($entry['api_key'])];
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
     * @throws InputException
     */
    private function getMerchantId(string $companyId): string
    {
        $jwt = $this->getJwt($companyId);
        return (string)$jwt['merchantId'];
    }

    /**
     * @param string $companyId
     * @return string
     * @throws InputException
     */
    private function getEndpoint(string $companyId): string
    {
        $jwt = $this->getJwt($companyId);
        return (string)$jwt['aud'];
    }
}
