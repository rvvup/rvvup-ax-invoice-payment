<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\HeaderDataInterface;
use Rvvup\AxInvoicePayment\Api\ResponseDataInterface;

class ResponseData extends DataObject implements ResponseDataInterface
{

    /**
     * @inheritDoc
     */
    public function getHeader(): HeaderDataInterface
    {
        return $this->getData(self::HEADER);
    }

    /**
     * @inheritDoc
     */
    public function setHeader(HeaderDataInterface $headerData): ResponseDataInterface
    {
        return $this->setData(self::HEADER, $headerData);
    }

    /**
     * @inheritDoc
     */
    public function getInvoices(): array
    {
        return $this->getData(self::INVOICES);
    }

    /**
     * @inheritDoc
     */
    public function setInvoices(array $invoices): ResponseDataInterface
    {
        return $this->setData(self::INVOICES, $invoices);
    }
}
