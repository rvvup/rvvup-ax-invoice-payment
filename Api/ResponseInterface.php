<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Api;

use Rvvup\AxInvoicePayment\Api\ResponseDataInterface;

interface ResponseInterface
{
    const RESPONSE = 'Response';

    /**
     * @return \Rvvup\AxInvoicePayment\Api\ResponseDataInterface
     */
    public function getResponse(): ResponseDataInterface;

    /**
     * @param ResponseDataInterface $response
     * @return ResponseInterface
     */
    public function setResponse(ResponseDataInterface $response): ResponseInterface;
}
