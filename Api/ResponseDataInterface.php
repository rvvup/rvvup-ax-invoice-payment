<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\HeaderDataInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;

interface ResponseDataInterface
{
    const HEADER = 'Header';
    const INVOICES = 'Invoices';

    /**
     * @return \Rvvup\AxInvoicePayment\Api\HeaderDataInterface
     */
    public function getHeader(): HeaderDataInterface;

    /**
     * @param HeaderDataInterface $headerData
     * @return ResponseDataInterface
     */
    public function setHeader(HeaderDataInterface $headerData): ResponseDataInterface;

    /**
     * @return \Rvvup\AxInvoicePayment\Api\InvoiceDataInterface[]
     */
    public function getInvoices(): array;

    /**
     * @param \Rvvup\AxInvoicePayment\Api\InvoiceDataInterface[] $invoices
     * @return ResponseDataInterface
     */
    public function setInvoices(array $invoices): ResponseDataInterface;
}
