<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface UpdateInvoiceInterface
{
    /**
     * @param string $id
     * @return void
     */
    public function updateInvoiceById(string $id): void;
}
