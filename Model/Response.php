<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\ResponseDataInterface;
use Rvvup\AxInvoicePayment\Api\ResponseInterface;

class Response extends DataObject implements ResponseInterface
{

    public function getResponse(): ResponseDataInterface
    {
        return $this->getData(self::RESPONSE);
    }

    public function setResponse(ResponseDataInterface $response): Response
    {
        return $this->setData(self::RESPONSE, $response);
    }
}
