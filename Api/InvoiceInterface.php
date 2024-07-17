<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;

interface InvoiceInterface
{
    const STATEMENT_ID = 'statementId';
    const ACCOUNT_ID = 'accountId';
    const INVOICES = 'invoices';

    /**
     * @return string|null
     */
    public function getStatementId(): ?string;

    /**
     * @param string $id
     * @return InvoiceInterface
     */
    public function setStatementId(string $id): InvoiceInterface;

    /**
     * @return string|null
     */
    public function getAccountId(): ?string;

    /**
     * @param string $id
     * @return InvoiceInterface
     */
    public function setAccountId(string $id): InvoiceInterface;

    /**
     * @return \Rvvup\AxInvoicePayment\Api\InvoiceDataInterface[]
     */
    public function getInvoices(): array;

    /**
     * @param \Rvvup\AxInvoicePayment\Api\InvoiceDataInterface[] $invoices
     * @return InvoiceInterface
     */
    public function setInvoices(array $invoices): InvoiceInterface;
}
