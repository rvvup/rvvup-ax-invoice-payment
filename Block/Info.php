<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Rvvup\AxInvoicePayment\Api\GetInvoice;

class Info extends Template
{
    /** @var SerializerInterface */
    private $json;

    /** @var GetInvoice */
    private $getInvoice;

    /**
     * @param Context $context
     * @param SerializerInterface $json
     * @param GetInvoice $getInvoice
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SerializerInterface $json,
        GetInvoice $getInvoice,
        array $data = []
    ) {
        $this->json = $json;
        $this->getInvoice = $getInvoice;
        parent::__construct($context,$data);
    }

    public function getInvoices(): array
    {
        if ($this->getInvoiceListId()) {
            return $this->getInvoice->getListOfInvoicesById($this->getInvoiceListId());
        } elseif ($this->getInvoiceId()) {
            return $this->getInvoice->getInvoiceById($this->getInvoiceId());
        }

        return [];
    }

    public function getInvoicesData(): string
    {
        $items = $this->getInvoices();
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

    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }
}
