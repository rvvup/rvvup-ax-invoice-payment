<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Redirect;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class In implements HttpGetActionInterface
{
    /** @var PageFactory */
    private $pageFactory;

    /** @var RequestInterface */
    private $request;

    /**
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     */
    public function __construct(
        PageFactory $pageFactory,
        RequestInterface $request
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        if ($this->request->getParam('checkout_id')) {
            $page->addHandle('axinvoice_success_index');
        }
        return $page;
    }
}
