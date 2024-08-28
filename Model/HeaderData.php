<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\HeaderDataInterface;

class HeaderData extends DataObject implements HeaderDataInterface
{

    /**
     * @inheritDoc
     */
    public function getAccountNumber(): string
    {
       return $this->getData(self::ACCOUNT_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setAccountNumber(string $accountNumber): HeaderDataInterface
    {
        return $this->setData(self::ACCOUNT_NUMBER, $accountNumber);
    }
}
