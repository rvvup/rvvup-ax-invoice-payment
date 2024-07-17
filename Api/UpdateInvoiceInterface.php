<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface UpdateInvoiceInterface
{
    /**
     * @param string $invoiceId
     * @param string $accountId
     * @param string $companyId
     * @return void
     */
    public function updateInvoiceById(string $invoiceId, string $accountId, string $companyId): void;
}
