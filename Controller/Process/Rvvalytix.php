<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Process;

use DateTime;
use Laminas\Http\Request;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Rvvup\AxInvoicePayment\Model\Config\RvvupConfigProvider;
use Rvvup\AxInvoicePayment\Model\UserAgentBuilder;
use Rvvup\AxInvoicePayment\Sdk\Curl;

class Rvvalytix implements HttpPostActionInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var Curl */
    private $curl;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var UserAgentBuilder */
    private $userAgentBuilder;

    /** @var RvvupConfigProvider */
    private $rvvupConfigProvider;

    /** @var RemoteAddress */
    private $remoteAddress;

    /**
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param UserAgentBuilder $userAgentBuilder
     * @param RvvupConfigProvider $rvvupConfigProvider
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        LoggerInterface     $logger,
        Curl                $curl,
        RequestInterface    $request,
        ResultFactory       $resultFactory,
        UserAgentBuilder    $userAgentBuilder,
        RvvupConfigProvider $rvvupConfigProvider,
        RemoteAddress       $remoteAddress
    )
    {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->userAgentBuilder = $userAgentBuilder;
        $this->rvvupConfigProvider = $rvvupConfigProvider;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $companyId = $this->request->getParam('company_id');
        $accountNumber = $this->request->getParam('account_number');
        $invoiceId = $this->request->getParam('invoice_id');
        $source = $this->request->getParam('source');
        $userAgent = $this->request->getParam('user_agent');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $ipAddress = $this->remoteAddress->getRemoteAddress();

        try {
            $type = $this->mapType($this->request->getParam('type'));
            $this->sendEvent(
                $companyId,
                $accountNumber,
                $invoiceId,
                $source,
                $userAgent,
                $type,
                $ipAddress
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
     * @param string $source
     * @param string $userAgent
     * @param string $type
     * @param string $ipAddress
     * @throws InputException
     */
    private function sendEvent(
        string $companyId,
        string $accountId,
        string $invoiceId,
        string $source,
        string $userAgent,
        string $type,
        string $ipAddress
    )
    {
        $rvvupConfig = $this->rvvupConfigProvider->getConfig($companyId);

        $merchantId = $rvvupConfig['merchant_id'];
        $baseUrl = $rvvupConfig['endpoint'];

        $params = $this->buildRequestData($merchantId, $companyId, $accountId, $invoiceId, $source, $userAgent, $type, $rvvupConfig['auth_token'], $ipAddress);
        $this->curl->request(
            Request::METHOD_POST,
            str_replace('graphql', "rvvalytix", $baseUrl),
            $params
        );
    }

    /**
     * @param string $merchantId
     * @param string $companyId
     * @param string $accountId
     * @param string $invoiceId
     * @param string $source
     * @param string $userAgent
     * @param string $type
     * @param string $authToken
     * @param string $ipAddress
     * @return array
     */
    private function buildRequestData(
        string $merchantId,
        string $companyId,
        string $accountId,
        string $invoiceId,
        string $source,
        string $userAgent,
        string $type,
        string $authToken,
        string $ipAddress
    ): array
    {
        $postData = [
            "events" => [
                [
                    "merchantId" => $merchantId,
                    "name" => $type,
                    "data" => [
                        "companyId" => $companyId,
                        "accountId" => $accountId,
                        "invoiceId" => $invoiceId,
                        "source" => $source,
                        "customerUserAgent" => $userAgent,
                        "customerIpAddress" => $ipAddress,
                    ],
                    "originCreatedAt" => (new DateTime('now'))->format('Y-m-d\TH:i:s.vP'),
                ]
            ],
        ];
        $headers = $this->getHeaders($authToken);

        return [
            'headers' => $headers,
            'json' => $postData
        ];
    }

    /**
     * @param string $authToken
     * @return string[]
     */
    private function getHeaders(string $authToken): array
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: ' . $this->userAgentBuilder->get(),
            'Authorization: Bearer ' . $authToken,
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
                return "ACCOUNT_STATEMENT_PLUGIN_PAGE_PAY_SELECTED";
        }
        throw new InputException(__('Invalid type ' . $type));
    }
}
