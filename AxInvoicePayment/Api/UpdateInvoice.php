<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface UpdateInvoice
{
    /**
     * @param int $id
     * @return void
     */
    public function updateInvoiceById(int $id): void;
}
