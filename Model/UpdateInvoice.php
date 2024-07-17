<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Rvvup\AxInvoicePayment\Api\UpdateInvoiceInterface;

class UpdateInvoice implements UpdateInvoiceInterface
{
    /**
     * @inheritDoc
     */
    public function updateInvoiceById(string $invoiceId, string $accountId, string $companyId): void
    {
        // TODO: Implement updateInvoiceById() method.
    }
}
