<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\InvoiceInterface;

interface GetInvoiceInterface
{
    /**
     * @param string $id
     * @return InvoiceInterface
     */
    public function getListOfInvoicesById(string $id): InvoiceInterface;
}
