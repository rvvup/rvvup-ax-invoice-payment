<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface GetInvoiceInterface
{
    /**
     * @param string $id
     * @return array
     */
    public function getListOfInvoicesById(string $id): array;
}
