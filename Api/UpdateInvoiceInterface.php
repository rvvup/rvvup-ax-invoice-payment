<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface UpdateInvoiceInterface
{
    /**
     * @param string $data
     * @return array
     */
    public function updateInvoiceById(string $data): array;
}
