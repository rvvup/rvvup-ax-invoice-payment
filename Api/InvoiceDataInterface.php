<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface;

interface InvoiceDataInterface
{
    const REFERENCE = 'reference';
    const INVOICE_DATE = 'invoiceDate';
    const TOTAL = 'total';
    const AMOUNT_REMAINING = 'amountRemaining';
    const AMOUNT_PAID = 'amountPaid';

    /**
     * @return string|null
     */
    public function getReference(): ?string;

    /**
     * @param string $reference
     * @return InvoiceDataInterface
     */
    public function setReference(string $reference): InvoiceDataInterface;

    /**
     * @return string|null
     */
    public function getInvoiceDate(): ?string;

    /**
     * @param string $invoiceDate
     * @return InvoiceDataInterface
     */
    public function setInvoiceDate(string $invoiceDate): InvoiceDataInterface;

    /**
     * @return \Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface
     */
    public function getTotal(): InvoiceAmountInterface;

    /**
     * @param InvoiceAmountInterface $total
     * @return InvoiceDataInterface
     */
    public function setTotal(InvoiceAmountInterface $total): InvoiceDataInterface;

    /**
     * @return \Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface
     */
    public function getAmountRemaining(): InvoiceAmountInterface;

    /**
     * @param InvoiceAmountInterface $amountRemaining
     * @return InvoiceDataInterface
     */
    public function setAmountRemaining(InvoiceAmountInterface $amountRemaining): InvoiceDataInterface;

    /**
     * @return \Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface
     */
    public function getAmountPaid(): InvoiceAmountInterface;

    /**
     * @param InvoiceAmountInterface $amountPaid
     * @return InvoiceDataInterface
     */
    public function setAmountPaid(InvoiceAmountInterface $amountPaid): InvoiceDataInterface;
}
