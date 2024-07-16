<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block;

use Magento\Framework\View\Element\Template;

class Info extends Template
{
    /**
     * @param string $param
     * @return string|null
     */
    public function getParam(string $param): ?string
    {
        return $this->_request->getParam($param) ?? null;
    }

}
