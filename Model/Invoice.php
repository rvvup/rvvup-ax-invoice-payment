<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\InvoiceInterface;

class Invoice extends DataObject implements InvoiceInterface
{

    public function getStatementId(): ?string
    {
        return $this->getData(self::STATEMENT_ID);
    }

    public function setStatementId(string $id): InvoiceInterface
    {
        return $this->setData(self::STATEMENT_ID, $id);
    }

    public function getAccountId(): ?string
    {
        return $this->getData(self::ACCOUNT_ID);
    }

    public function setAccountId(string $id): InvoiceInterface
    {
        return $this->setData(self::ACCOUNT_ID, $id);
    }

    public function getInvoices(): array
    {
        return $this->getData(self::INVOICES);
    }

    public function setInvoices(array $invoices): InvoiceInterface
    {
        return $this->setData(self::INVOICES, $invoices);
    }
}
