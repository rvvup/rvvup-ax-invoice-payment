<?php

namespace Rvvup\AxInvoicePayment\Api;

interface HeaderDataInterface
{
    const ACCOUNT_NUMBER = 'AccountNum';

    /**
     * @return string
     */
    public function getAccountNumber(): string;

    /**
     * @param string $accountNumber
     * @return HeaderDataInterface
     */
    public function setAccountNumber(string $accountNumber): HeaderDataInterface;

}
