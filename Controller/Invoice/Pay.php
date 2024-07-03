<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Invoice;

use Laminas\Http\Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Rvvup\Payments\Model\ConfigInterface;
use Rvvup\Payments\Sdk\Curl;

class Pay implements HttpPostActionInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ConfigInterface */
    private $config;

    /** @var Curl */
    private $curl;

    /** @var SerializerInterface */
    private $json;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param SerializerInterface $json
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        Curl $curl,
        SerializerInterface $json,
        RequestInterface $request,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->curl = $curl;
        $this->json = $json;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $amount = $this->request->getParam('amount');
        $storeId = $this->request->getParam('store_id');
        $currencyCode = $this->request->getParam('currency_code');
        $invoices = $this->request->getParam('invoices');
        $selectedInvoices = $this->request->getParam('selected_invoices');
        $displayId = $this->request->getParam('display_id');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->config->isActive(ScopeInterface::SCOPE_STORE, $storeId)) {
            $result->setData([
                'success' => false,
                'message' => 'Please contact store administrator'
            ]);
            return $result;
        }

        try {
            $body = $this->createCheckout($amount, $storeId, $currencyCode, $invoices, $selectedInvoices, $displayId);
            $result->setData([
                'iframe-url' => $body['url'],
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

    private function createCheckout(
        string $amount,
        string $storeId,
        string $currencyCode,
        string $invoices,
        string $selectedInvoices,
        string $displayId
    ): array {
        $params = $this->buildRequestData($amount, $storeId, $currencyCode, $invoices, $selectedInvoices, $displayId);
        $request = $this->curl->request(Request::METHOD_POST, $this->getApiUrl($storeId), $params);
        $body = $this->json->unserialize($request->body);
        return $body;
    }

    /**
     * @param string $amount
     * @param string $storeId
     * @param string $currencyCode
     * @param string $invoices
     * @param string $selectedInvoices
     * @return array
     * @throws NoSuchEntityException
     */
    private function buildRequestData(
        string $amount,
        string $storeId,
        string $currencyCode,
        string $invoices,
        string $selectedInvoices,
        string $displayId
    ): array {
        $url = $this->storeManager->getStore($storeId)->getBaseUrl(
                UrlInterface::URL_TYPE_WEB,
                true
            )
            . "axinvoice/redirect/in?store_id=$storeId&checkout_id={{CHECKOUT_ID}}";

        $postData = [
            'amount' => ['amount' => $amount, 'currency' => $currencyCode],
            'metadata' => [
                'invoices' => $invoices,
                'selected_invoices' => $selectedInvoices,
                'display_id' => $displayId,
                'store_id' => $storeId
            ],
            'source' => 'MAGENTO_AX_INVOICE',
            'successUrl' => $url
        ];

        $headers = $this->getHeaders($storeId);

        return [
            'headers' => $headers,
            'json' => $postData
        ];
    }

    /**
     * @param string $storeId
     * @return string[]
     */
    private function getHeaders(string $storeId): array
    {
        $token = $this->config->getJwtConfig(ScopeInterface::SCOPE_STORE, $storeId);
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ];
    }

    /** @todo move to rest api sdk
     * @param string $storeId
     * @return string
     */
    private function getApiUrl(string $storeId): string
    {
        $merchantId = $this->config->getMerchantId(ScopeInterface::SCOPE_STORE, $storeId);
        $baseUrl = $this->config->getEndpoint(ScopeInterface::SCOPE_STORE, $storeId);
        return str_replace('graphql', "api/2024-03-01/$merchantId/checkouts", $baseUrl);
    }
}
