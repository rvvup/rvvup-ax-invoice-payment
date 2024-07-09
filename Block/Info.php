<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block;

use Laminas\Http\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Rvvup\AxInvoicePayment\Api\GetInvoice;
use Rvvup\Payments\Model\ConfigInterface;
use Magento\Framework\DataObjectFactory;
use Rvvup\Payments\Sdk\Curl;

class Info extends Template
{
    /** @var SerializerInterface */
    private $json;

    /** @var GetInvoice */
    private $getInvoice;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /** @var ConfigInterface */
    private $config;

    /** @var Curl */
    private $curl;

    /**
     * @param Context $context
     * @param SerializerInterface $json
     * @param GetInvoice $getInvoice
     * @param DataObjectFactory $dataObjectFactory
     * @param Curl $curl
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SerializerInterface $json,
        GetInvoice $getInvoice,
        DataObjectFactory $dataObjectFactory,
        Curl $curl,
        ConfigInterface $config,
        array $data = []
    ) {
        $this->json = $json;
        $this->getInvoice = $getInvoice;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->config = $config;
        $this->curl = $curl;
        parent::__construct($context,$data);
    }

    public function getInvoices(): array
    {
        if ($this->isReturnUrlUsed()) {
            return $this->getDisplayData();
        }

        if ($this->getInvoiceListId()) {
            $invoices = $this->getInvoice->getListOfInvoicesById($this->getInvoiceListId());
        } elseif ($this->getInvoiceId()) {
            $invoices = $this->getInvoice->getInvoiceById($this->getInvoiceId());
        }

        return $invoices ?? [];
    }

    public function getInvoicesData(): string
    {
        $items = $this->getInvoices();
        if ($this->isReturnUrlUsed()) {
            $items = array_first($items);
        }
        $itemsData = array_map(function ($item) {
            return $item->getData();
        }, $items);

        return $this->json->serialize($itemsData);
    }

    public function getInvoiceId(): ?int
    {
        return (int)$this->_request->getParam('ax_invoice_id') ?? null;
    }

    public function getInvoiceListId() :?int
    {
        return (int)$this->_request->getParam('ax_invoice_list_id') ?? null;
    }

    public function getDisplayId(): ?int
    {
        return $this->getInvoiceListId() ?: $this->getInvoiceId();
    }

    public function getInvoiceParameterUsed(): string
    {
        return $this->getInvoiceListId() ? 'ax_invoice_list_id': 'ax_invoice_id';
    }


    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

    public function isReturnUrlUsed(): bool
    {
        return (bool)$this->_request->getParam('checkout_id');
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
                $displayId = (int) $metadata['display_id'];
                $payedInvoices = $this->json->unserialize($metadata['selected_invoices']);
                $invoiceParameter = $metadata['invoice_parameter'];
                if ($invoiceParameter == 'ax_invoice_list_id') {
                    $invoices = $this->getInvoice->getListOfInvoicesById($displayId);
                } elseif ($invoiceParameter == 'ax_invoice_id') {
                    $invoices = $this->getInvoice->getInvoiceById($displayId);
                }

                $displayInvoices = [];
                foreach ($invoices ?? [] as $item) {
                    $invoice = $item->getData();
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
