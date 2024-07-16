<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Rvvup\AxInvoicePayment\Api\GetInvoiceInterface;

class GetInvoice implements GetInvoiceInterface
{
    /**
     * @inheritDoc
     */
    public function getListOfInvoicesById(string $id): array
    {
        $items = [];
        foreach (range(1, $id) as $item) {
            $items[] = [
                'reference' => (string)$item,
                'invoiceDate' => date('Y-m-d\TH:i:sP', strtotime('now')),
                "total" => [
                    "amount" => (string)($item * 100),
                    "currency" => "GBP"
                ],
                "amountRemaining" => [
                    "amount" => (string)($item * 100),
                    "currency" => "GBP"
                ],
                "amountPaid" => [
                    "amount" => '0',
                    "currency" => "GBP"
                ],
            ];
        }
        $data = [
            'statementId' => $id,
            'accountId' => 'accountId',
            'invoices' => $items
        ];
        return [$data];
    }
}
