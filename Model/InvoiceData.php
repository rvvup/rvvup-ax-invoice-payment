<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;

class InvoiceData extends DataObject implements InvoiceDataInterface
{

    /**
     * @inheritDoc
     */
    public function getInvoiceNum(): string
    {
        return $this->getData(self::INVOICE_NUM);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceNum(string $invoiceNum): InvoiceDataInterface
    {
        return $this->setData(self::INVOICE_NUM, $invoiceNum);
    }

    /**
     * @inheritDoc
     */
    public function getTransDate(): string
    {
        return $this->getData(self::TRANS_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setTransDate(string $transDate): InvoiceDataInterface
    {
        return $this->setData(self::TRANS_DATE, $transDate);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceType(): string
    {
        return $this->getData(self::INVOICE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceType(string $invoiceType): InvoiceDataInterface
    {
        return $this->setData(self::INVOICE_TYPE, $invoiceType);
    }

    /**
     * @inheritDoc
     */
    public function getSalesOrderNo(): string
    {
        return $this->getData(self::SALES_ORDER_NO);
    }

    /**
     * @inheritDoc
     */
    public function setSalesOrderNo(string $salesOrderNo): InvoiceDataInterface
    {
        return $this->setData(self::SALES_ORDER_NO, $salesOrderNo);
    }

    /**
     * @inheritDoc
     */
    public function getChannelDept(): string
    {
        return $this->getData(self::CHANNEL_DEPT);
    }

    /**
     * @inheritDoc
     */
    public function setChannelDept(string $channelDept): InvoiceDataInterface
    {
        return $this->setData(self::CHANNEL_DEPT, $channelDept);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerRef(): string
    {
        return $this->getData(self::CUSTOMER_REF);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerRef(string $customerRef): InvoiceDataInterface
    {
        return $this->setData(self::CUSTOMER_REF, $customerRef);
    }

    /**
     * @inheritDoc
     */
    public function getTotalNet(): float
    {
        return $this->getData(self::TOTAL_NET);
    }

    /**
     * @inheritDoc
     */
    public function setTotalNet(float $totalNet): InvoiceDataInterface
    {
        return $this->setData(self::TOTAL_NET, $totalNet);
    }

    /**
     * @inheritDoc
     */
    public function getTotalVAT(): float
    {
        return $this->getData(self::TOTAL_VAT);
    }

    /**
     * @inheritDoc
     */
    public function setTotalVAT(float $totalVAT): InvoiceDataInterface
    {
        return $this->setData(self::TOTAL_VAT, $totalVAT);
    }

    /**
     * @inheritDoc
     */
    public function getTotalGross(): float
    {
        return $this->getData(self::TOTAL_GROSS);
    }

    /**
     * @inheritDoc
     */
    public function setTotalGross(float $totalGross): InvoiceDataInterface
    {
        return $this->setData(self::TOTAL_GROSS, $totalGross);
    }

    /**
     * @inheritDoc
     */
    public function getOutstanding(): float
    {
        return $this->getData(self::OUTSTANDING);
    }

    /**
     * @inheritDoc
     */
    public function setOutstanding(float $outstanding): InvoiceDataInterface
    {
        return $this->setData(self::OUTSTANDING, $outstanding);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency(string $currency): InvoiceDataInterface
    {
        return $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @inheritDoc
     */
    public function getDueDate(): string
    {
        return $this->getData(self::DUE_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setDueDate(string $dueDate): InvoiceDataInterface
    {
        return $this->setData(self::DUE_DATE, $dueDate);
    }
}
