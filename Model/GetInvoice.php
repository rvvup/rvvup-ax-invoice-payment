<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Rvvup\AxInvoicePayment\Api\GetInvoiceInterface;
use Rvvup\AxInvoicePayment\Api\HeaderDataInterface;
use Rvvup\AxInvoicePayment\Api\HeaderDataInterfaceFactory;
use Rvvup\AxInvoicePayment\Api\ResponseDataInterface;
use Rvvup\AxInvoicePayment\Api\ResponseDataInterfaceFactory;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterface;
use Rvvup\AxInvoicePayment\Api\InvoiceDataInterfaceFactory;
use Rvvup\AxInvoicePayment\Api\ResponseInterface;
use Rvvup\AxInvoicePayment\Api\ResponseInterfaceFactory;

class GetInvoice implements GetInvoiceInterface
{
    /** @var HeaderDataInterfaceFactory */
    private $headerDataInterfaceFactory;

    /** @var InvoiceDataInterfaceFactory  */
    private $invoiceDataInterfaceFactory;

    /** @var ResponseInterfaceFactory */
    private $responseInterfaceFactory;

    /** @var DateTimeFactory */
    private $dateTimeFactory;

    /** @var ResponseDataInterfaceFactory */
    private $responseDataInterfaceFactory;

    /**
     * @param HeaderDataInterfaceFactory $headerDataInterfaceFactory
     * @param ResponseDataInterfaceFactory $responseDataInterfaceFactory
     * @param InvoiceDataInterfaceFactory $invoiceDataInterfaceFactory
     * @param ResponseInterfaceFactory $responseInterfaceFactory
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        HeaderDataInterfaceFactory $headerDataInterfaceFactory,
        ResponseDataInterfaceFactory $responseDataInterfaceFactory,
        InvoiceDataInterfaceFactory $invoiceDataInterfaceFactory,
        ResponseInterfaceFactory $responseInterfaceFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->headerDataInterfaceFactory = $headerDataInterfaceFactory;
        $this->responseDataInterfaceFactory = $responseDataInterfaceFactory;
        $this->invoiceDataInterfaceFactory = $invoiceDataInterfaceFactory;
        $this->responseInterfaceFactory = $responseInterfaceFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritDoc
     */
    public function getListOfInvoicesById(string $companyId, string $accountId): ResponseInterface
    {
        $items = [];
        foreach (range(1, 5) as $item) {
            /** @var InvoiceDataInterface $invoice */
            $invoice = $this->invoiceDataInterfaceFactory->create();
            $invoice->setInvoiceNum('IN' . $item);
            /** @var DateTime $date */
            $date = $this->dateTimeFactory->create();
            $date = $date->date('Y-m-d\TH:i:s');
            $invoice->setTransDate($date);
            $invoice->setInvoiceType('Invoice');
            $invoice->setSalesOrderNo('1234-RS1234T12-' . $item);
            $invoice->setChannelDept('Store 1');
            $invoice->setCustomerRef('111');
            $invoice->setTotalNet((float)($item * 100));
            $invoice->setTotalVAT((float)($item * 100));
            $invoice->setTotalGross((float)($item * 200));
            $invoice->setOutstanding((float)($item * 200));
            $invoice->setCurrency('GBP');
            $invoice->setDueDate($date);

            $items[] = $invoice;
        }

        /** @var HeaderDataInterface $headerData */
        $headerData = $this->headerDataInterfaceFactory->create();
        $headerData->setAccountNumber($accountId);

        /** @var ResponseInterface $result */
        $result = $this->responseInterfaceFactory->create();
        /** @var ResponseDataInterface $responseData */
        $responseData = $this->responseDataInterfaceFactory->create();
        $responseData->setInvoices($items);
        $responseData->setHeader($headerData);

        $result->setResponse($responseData);
        return $result;
    }
}
