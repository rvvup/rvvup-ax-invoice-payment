<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface InvoiceAmountInterface
{
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';


    /**
     * @return string|null
     */
    public function getAmount(): ?string;

    /**
     * @param string $amount
     * @return InvoiceAmountInterface
     */
    public function setAmount(string $amount): InvoiceAmountInterface;

    /**
     * @return string|null
     */
    public function getCurrency(): ?string;

    /**
     * @param string $currency
     * @return InvoiceAmountInterface
     */
    public function setCurrency(string $currency): InvoiceAmountInterface;
}
