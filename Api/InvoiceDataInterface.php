<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface InvoiceDataInterface
{
    const INVOICE_NUM = 'InvoiceNum';
    const TRANS_DATE = 'TransDate';
    const INVOICE_TYPE = 'InvoiceType';
    const SALES_ORDER_NO = 'SalesOrderNo';
    const CHANNEL_DEPT = 'ChannelDept';
    const CUSTOMER_REF = 'CustomerRef';
    const TOTAL_NET = 'TotalNet';
    const TOTAL_VAT = 'TotalVat';
    const TOTAL_GROSS = 'TotalGross';
    const OUTSTANDING = 'Outstanding';
    const CURRENCY = 'Currency';
    const DUE_DATE = 'DueDate';

    /**
     * @return string
     */
    public function getInvoiceNum(): string;

    /**
     * @param string $invoiceNum
     * @return InvoiceDataInterface
     */
    public function setInvoiceNum(string $invoiceNum): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getTransDate(): string;

    /**
     * @param string $transDate
     * @return InvoiceDataInterface
     */
    public function setTransDate(string $transDate): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getInvoiceType(): string;

    /**
     * @param string $invoiceType
     * @return InvoiceDataInterface
     */
    public function setInvoiceType(string $invoiceType): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getSalesOrderNo(): string;

    /**
     * @param string $salesOrderNo
     * @return InvoiceDataInterface
     */
    public function setSalesOrderNo(string $salesOrderNo): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getChannelDept(): string;

    /**
     * @param string $channelDept
     * @return InvoiceDataInterface
     */
    public function setChannelDept(string $channelDept): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getCustomerRef(): string;

    /**
     * @param string $customerRef
     * @return InvoiceDataInterface
     */
    public function setCustomerRef(string $customerRef): InvoiceDataInterface;

    /**
     * @return float
     */
    public function getTotalNet(): float;

    /**
     * @param float $totalNet
     * @return InvoiceDataInterface
     */
    public function setTotalNet(float $totalNet): InvoiceDataInterface;

    /**
     * @return float
     */
    public function getTotalVAT(): float;

    /**
     * @param float $totalVAT
     * @return InvoiceDataInterface
     */
    public function setTotalVAT(float $totalVAT): InvoiceDataInterface;

    /**
     * @return float
     */
    public function getTotalGross(): float;

    /**
     * @param float $totalGross
     * @return InvoiceDataInterface
     */
    public function setTotalGross(float $totalGross): InvoiceDataInterface;

    /**
     * @return float
     */
    public function getOutstanding(): float;

    /**
     * @param float $outstanding
     * @return InvoiceDataInterface
     */
    public function setOutstanding(float $outstanding): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return InvoiceDataInterface
     */
    public function setCurrency(string $currency): InvoiceDataInterface;

    /**
     * @return string
     */
    public function getDueDate(): string;

    /**
     * @param string $dueDate
     * @return InvoiceDataInterface
     */
    public function setDueDate(string $dueDate): InvoiceDataInterface;

}
