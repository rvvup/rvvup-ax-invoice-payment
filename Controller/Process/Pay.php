<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Process;

use Laminas\Http\Request;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Rvvup\AxInvoicePayment\Model\Config\RvvupConfigProvider;
use Rvvup\AxInvoicePayment\Model\UserAgentBuilder;
use Rvvup\AxInvoicePayment\Sdk\Curl;

class Pay implements HttpPostActionInterface
{
    public const PAYMENT_RVVUP_AX_INTEGRATION = 'payment/rvvup_ax_integration';
    /** @var LoggerInterface */
    private $logger;

    /** @var Curl */
    private $curl;

    /** @var SerializerInterface */
    private $json;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var UserAgentBuilder */
    private $userAgentBuilder;

    /** @var RvvupConfigProvider */
    private $rvvupConfigProvider;

    /**
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param SerializerInterface $json
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param UserAgentBuilder $userAgentBuilder
     * @param RvvupConfigProvider $rvvupConfigProvider
     */
    public function __construct(
        LoggerInterface     $logger,
        Curl                $curl,
        SerializerInterface $json,
        RequestInterface    $request,
        ResultFactory       $resultFactory,
        UserAgentBuilder    $userAgentBuilder,
        RvvupConfigProvider $rvvupConfigProvider
    )
    {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->json = $json;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->userAgentBuilder = $userAgentBuilder;
        $this->rvvupConfigProvider = $rvvupConfigProvider;
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

        try {
            $url = $this->createAccountStatement($companyId, $accountNumber, $invoiceId);
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
     * @param string $companyId
     * @param string $accountId
     * @param string $invoiceId
     * @return string
     * @throws InputException
     */
    private function createAccountStatement(
        string $companyId,
        string $accountId,
        string $invoiceId
    ): string
    {
        $rvvupConfig = $this->rvvupConfigProvider->getConfig($companyId);
        $merchantId = $rvvupConfig['merchant_id'];
        $baseUrl = $rvvupConfig['endpoint'];

        $params = $this->buildRequestData($companyId, $accountId, $invoiceId, $rvvupConfig['auth_token']);
        $request = $this->curl->request(
            Request::METHOD_POST,
            str_replace('graphql', "api/2024-03-01/$merchantId/accounts/statements", $baseUrl),
            $params
        );
        $body = $this->json->unserialize($request->body);
        if (isset($body['url'])) {
            return $body['url'];
        };
        throw new InputException(__('Missing returnUrl from Rvvup'));
    }

    /**
     * @param string $companyId
     * @param string $accountId
     * @param string $invoiceId
     * @param string $authToken
     * @return array
     */
    private function buildRequestData(
        string $companyId,
        string $accountId,
        string $invoiceId,
        string $authToken
    ): array
    {
        $postData = [
            "connection" => [
                "type" => "MAGENTO_PROXY",
                "companyId" => $companyId,
                "accountId" => $accountId,
                "invoiceId" => $invoiceId
            ],
            'reference' => $accountId
        ];
        $headers = $this->getHeaders($accountId, $companyId, $authToken);

        return [
            'headers' => $headers,
            'json' => $postData
        ];
    }

    /**
     * @param string $accountId
     * @param string $companyId
     * @param string $authToken
     * @return string[]
     */
    private function getHeaders(
        string $accountId,
        string $companyId,
        string $authToken
    ): array
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken,
            'Idempotency-Key: ' . $accountId . $companyId,
            'User-Agent: ' . $this->userAgentBuilder->get()
        ];
    }
}
