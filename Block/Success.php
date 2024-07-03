<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block;

use Laminas\Http\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Rvvup\Payments\Model\ConfigInterface;
use Rvvup\Payments\Sdk\Curl;

class Success extends Template
{
    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /** @var SerializerInterface */
    private $json;

    /** @var ConfigInterface */
    private $config;

    /** @var Curl */
    private $curl;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param SerializerInterface $json
     * @param Context $context
     * @param Curl $curl
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        SerializerInterface $json,
        Template\Context $context,
        Curl $curl,
        ConfigInterface $config,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->json = $json;
        $this->config = $config;
        $this->curl = $curl;
        parent::__construct($context,$data);
    }

    /**
     * @return array
     */
    public function getDisplayData(): array
    {
        $checkoutId = $this->_request->getParam('checkout_id');
        $storeId = $this->_request->getParam('store_id');
        if ($checkoutId && $storeId) {
            try {
                $data = $this->getCheckoutData($checkoutId, $storeId);
                $metadata = $data['metadata'];
                $invoices = $this->json->unserialize($metadata['invoices']);
                $displayId = $metadata['display_id'];
                $payedInvoices = $this->json->unserialize($metadata['selected_invoices']);
                $displayInvoices = [];
                foreach ($invoices as $invoice) {
                    if (!$invoice['is_payed']) {
                        if (in_array($invoice['invoice_number'], $payedInvoices)) {
                            $invoice['is_payed'] = 1;
                            $invoice['is_recent'] = true;
                        }
                    }
                    $displayInvoices[] = $this->dataObjectFactory->create(['data' => $invoice]);
                }
                return [$displayInvoices, $displayId];
            } catch (\Exception $ex) {
                $this->_logger->error($ex->getMessage());
                return [[], null];
            }
        }
        return [[], null];
    }

    /**
     * @param string $checkoutId
     * @param string|null $storeId
     * @return array
     */
    public function getCheckoutData(
        string $checkoutId,
        ?string $storeId
    ): array {
        $params =  ['headers' => $this->getHeaders($storeId), 'json' => ''];
        $request = $this->curl->request(Request::METHOD_GET, $this->getApiUrl($storeId, $checkoutId), $params);
        $body = $this->json->unserialize($request->body);
        return $body;
    }

    /**
     * @param string|null $storeId
     * @return string[]
     */
    private function getHeaders(?string $storeId): array
    {
        $token = $this->config->getJwtConfig(ScopeInterface::SCOPE_STORE, $storeId);
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ];
    }

    /** @param string|null $storeId
     * @param string $checkoutId
     * @return string
     * @todo move to rest api sdk
     */
    private function getApiUrl(?string $storeId, string $checkoutId): string
    {
        $merchantId = $this->config->getMerchantId(ScopeInterface::SCOPE_STORE, $storeId);
        $baseUrl = $this->config->getEndpoint(ScopeInterface::SCOPE_STORE, $storeId);
        return str_replace(
            'graphql',
            "api/2024-03-01/$merchantId/checkouts/$checkoutId",
            $baseUrl
        );
    }
}
