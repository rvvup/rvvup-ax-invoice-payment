<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

interface GetInvoice
{
    /**
     * @param int $id
     * @return array
     */
    public function getInvoiceById(int $id): array;

    /**
     * @param int $id
     * @return array
     */
    public function getListOfInvoicesById(int $id): array;
}
