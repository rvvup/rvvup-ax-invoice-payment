<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Rvvup\AxInvoicePayment\Controller\Index\Index;

class Router implements RouterInterface
{
    /** @var ActionFactory */
    private $actionFactory;

    /**
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        ActionFactory $actionFactory
    )
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * Match application action by request
     *
     * @param RequestInterface $request
     * @return ActionInterface
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');
        if (preg_match('#^statements/(\w+)/(\d+)/(\w+)$#', $identifier, $matches)) {
            $request->setModuleName('statement')
                ->setRouteName('statements')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('company_id', $matches[1])
                ->setParam('account_number', $matches[2])
                ->setParam('invoice_id', $matches[3]);
            return $this->actionFactory->create(Index::class);
        }
        return null;
    }
}
