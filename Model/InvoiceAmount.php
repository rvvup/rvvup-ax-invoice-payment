<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObject;
use Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface;

class InvoiceAmount extends DataObject implements InvoiceAmountInterface
{
    /**
     * @inheritDoc
     */
    public function getAmount(): ?string
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount(string $amount): InvoiceAmountInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): ?string
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency(string $currency): InvoiceAmountInterface
    {
        return $this->setData(self::CURRENCY, $currency);
    }
}
