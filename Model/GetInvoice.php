<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Rvvup\AxInvoicePayment\Api\GetInvoiceInterface;

use Rvvup\AxInvoicePayment\Api\InvoiceInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceInterfaceFactory;
use Rvvup\AxInvoicePayment\Api\InvoiceAmountInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceAmountInterfaceFactory;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterfaceFactory;

class GetInvoice implements GetInvoiceInterface
{
    /**
     * @var InvoiceInterfaceFactory
     */
    private $invoiceInterfaceFactory;

    /**
     * @var InvoiceAmountInterfaceFactory
     */
    private $invoiceAmountInterfaceFactory;

    /**
     * @var InvoiceDataInterfaceFactory
     */
    private $invoiceDataInterfaceFactory;

    /**
     * @param InvoiceInterfaceFactory $invoiceInterfaceFactory
     */
    public function __construct(
        InvoiceInterfaceFactory $invoiceInterfaceFactory,
        InvoiceAmountInterfaceFactory $invoiceAmountInterfaceFactory,
        InvoiceDataInterfaceFactory $invoiceDataInterfaceFactory
    ) {
        $this->invoiceInterfaceFactory = $invoiceInterfaceFactory;
        $this->invoiceAmountInterfaceFactory = $invoiceAmountInterfaceFactory;
        $this->invoiceDataInterfaceFactory = $invoiceDataInterfaceFactory;
    }
    /**
     * @inheritDoc
     */
    public function getListOfInvoicesById(string $id): InvoiceInterface
    {
        $items = [];
        foreach (range(1, $id) as $item) {
            /** @var InvoiceDataInterface $invoice */
            $invoice = $this->invoiceDataInterfaceFactory->create();
            $invoice->setReference((string) $item);
            $invoice->setInvoiceDate(date('Y-m-d\TH:i:sP', strtotime('now')));

            /** @var InvoiceAmountInterface $total */
            $total = $this->invoiceAmountInterfaceFactory->create();
            $total->setAmount((string)($item * 100));
            $total->setCurrency('GBP');
            $invoice->setTotal($total);

            $amountRemaining = $this->invoiceAmountInterfaceFactory->create();
            $amountRemaining->setAmount((string)($item * 100));
            $amountRemaining->setCurrency('GBP');
            $invoice->setAmountRemaining($amountRemaining);

            $amountPaid = $this->invoiceAmountInterfaceFactory->create();
            $amountPaid->setAmount('0');
            $amountPaid->setCurrency('GBP');
            $invoice->setAmountPaid($amountPaid);
            $items[] = $invoice;
        }

        $result = $this->invoiceInterfaceFactory->create();
        $result->setStatementId($id);
        $result->setCompanyId('companyId');
        $result->setAccountId('accountId');
        $result->setInvoices($items);
        return $result;
    }
}
