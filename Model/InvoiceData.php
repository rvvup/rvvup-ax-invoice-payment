<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;

class InvoiceData extends DataObject implements InvoiceDataInterface
{

    /**
     * @inheritDoc
     */
    public function getReference(): ?string
    {
        return $this->getData(self::REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setReference(string $reference): InvoiceDataInterface
    {
        return $this->setData(self::REFERENCE, $reference);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceDate(): ?string
    {
        return $this->getData(self::INVOICE_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceDate(string $invoiceDate): InvoiceDataInterface
    {
        return $this->setData(self::INVOICE_DATE, $invoiceDate);
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): InvoiceAmountInterface
    {
        return $this->getData(self::TOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setTotal(InvoiceAmountInterface $total): InvoiceDataInterface
    {
        return $this->setData(self::TOTAL, $total);
    }

    /**
     * @inheritDoc
     */
    public function getAmountRemaining(): InvoiceAmountInterface
    {
        return $this->getData(self::AMOUNT_REMAINING);
    }

    /**
     * @inheritDoc
     */
    public function setAmountRemaining(InvoiceAmountInterface $amountRemaining): InvoiceDataInterface
    {
        return $this->setData(self::AMOUNT_REMAINING, $amountRemaining);
    }

    /**
     * @inheritDoc
     */
    public function getAmountPaid(): InvoiceAmountInterface
    {
        return $this->getData(self::AMOUNT_PAID);
    }

    /**
     * @inheritDoc
     */
    public function setAmountPaid(InvoiceAmountInterface $amountPaid): InvoiceDataInterface
    {
        return $this->setData(self::AMOUNT_PAID, $amountPaid);
    }
}
