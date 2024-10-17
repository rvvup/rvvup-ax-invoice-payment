<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Rvvup\AxInvoicePayment\Controller\Process\Pay;

class Index implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /** @var ScopeConfigInterface */
    private $config;

    /**
     * @param PageFactory $pageFactory
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        PageFactory $pageFactory,
        ScopeConfigInterface $config
    ) {
        $this->pageFactory = $pageFactory;
        $this->config = $config;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        if ($this->config->getValue(Pay::PAYMENT_RVVUP_AX_INTEGRATION . '/active') != 1) {
            $page->getLayout()->getUpdate()->removeHandle('statements_index_index');
        }
        return $page;
    }
}
