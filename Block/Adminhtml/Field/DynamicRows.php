<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Block\Adminhtml\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class DynamicRows extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('company', ['label' => __('Company'), 'class' => 'required-entry']);
        $this->addColumn('jwt_key', ['label' => __('JWT Key'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Row');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $row->setData('option_extra_attrs', []);
    }

}
