<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model;

use Magento\Framework\DataObjectFactory;
class GetInvoice implements \Rvvup\AxInvoicePayment\Api\GetInvoice
{
    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceById(int $id): array
    {
        return [$this->dataObjectFactory->create(
            ['data' =>
                [
                    'date' => date('Y-m-d H:i:s', strtotime('now')),
                    'invoice_number' => $id,
                    'is_payed' => 0,
                    'total' => rand(0, 999),
                    'currency' => '£'
                ]
            ]
        )];
    }

    /**
     * @inheritDoc
     */
    public function getListOfInvoicesById(int $id): array
    {
        foreach (range(1, $id) as $item) {
            $items[] = $this->dataObjectFactory->create(
                ['data' =>
                    [
                        'date' => date('Y-m-d H:i:s', strtotime('now')),
                        'invoice_number' => $item,
                        'is_payed' => rand(0,1),
                        'total' => rand(0, 999),
                        'currency' => '£'
                    ]
                ]
            );
        }
        return $items;
    }
}
