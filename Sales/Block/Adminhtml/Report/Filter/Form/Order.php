<?php

namespace Icare\Sales\Block\Adminhtml\Report\Filter\Form;

use Magento\Sales\Block\Adminhtml\Report\Filter\Form;

/**
 * Overrides form filter to add more filter options
 * 
 * @author Nam Pham
 *
 */
class Order extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Order
{
    
    /**
     * Preparing form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        // decide if report/sales_report/sales to add more filter
        $req = $this->getRequest();
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');
        if ($req->getModuleName() == 'reports' && $req->getActionName() == 'sales' && 
            is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            //@todo populate agent options regarding to current store view
            $agentOptions = [];
            $fieldset->addField(
                'sales_agent', 
                'Icare\Sales\Block\Adminhtml\Report\Helper\Form\SalesAgent', 
                [
                    'name' => 'sales_agent',
                    'options' => $agentOptions,
                    'label' => __('Sales Agent')
                ]
            );
        }
        
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\Reports\Block\Adminhtml\Filter\Form::_initFormValues()
     */
    protected function _initFormValues()
    {
        
        return parent::_initFormValues();
    }
}