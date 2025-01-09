<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Info extends Template
{
    /**
     * Get admin config value for Pay Button Text
     *
     * @return string
     */
    public function getPayButtonText(): string
    {
        return $this->_scopeConfig->getValue(
            'payment/rvvup_ax_integration/button_text',
            ScopeInterface::SCOPE_STORE
        ) ?: 'Review Statement';
    }


    /**
     * Get admin config value for Loading Text
     *
     * @return string
     */
    public function getButtonLoadingText(): string
    {
        return $this->_scopeConfig->getValue(
            'payment/rvvup_ax_integration/button_loading_text',
            ScopeInterface::SCOPE_STORE
        ) ?: 'Loading statement details, please wait...';
    }

    /**
     * @param string $param
     * @return string|null
     */
    public function getParam(string $param): ?string
    {
        return $this->_request->getParam($param) ?? null;
    }

}
