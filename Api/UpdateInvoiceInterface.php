<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface UpdateInvoiceInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function updateInvoiceById(string $data): array;
}
