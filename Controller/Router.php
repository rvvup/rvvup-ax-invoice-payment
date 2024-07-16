<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ResponseInterface;
use Rvvup\AxInvoicePayment\Controller\Index\Index;

class Router implements RouterInterface
{
    /** @var ActionFactory */
    private $actionFactory;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
    }

    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if (preg_match('#^statement/(\d+)/(\d+)/([\d\w]+)$#', $identifier, $matches)) {
            $request->setModuleName('statement')
                ->setRouteName('statement')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('id', $matches[1])
                ->setParam('account_number', $matches[2])
                ->setParam('company_id', $matches[3]);
            return $this->actionFactory->create(Index::class);
        }
        return null;
    }
}
