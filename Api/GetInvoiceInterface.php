<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\ResponseInterface;

interface GetInvoiceInterface
{
    /**
     * @param string $companyId
     * @param string $accountId
     * @return ResponseInterface
     */
    public function getListOfInvoicesById(string $companyId, string $accountId): ResponseInterface;
}
