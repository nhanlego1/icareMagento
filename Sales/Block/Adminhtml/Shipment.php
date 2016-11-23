<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */
namespace Icare\Sales\Block\Adminhtml;

/**
 * Adminhtml sales shipments block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipment extends \Magento\Sales\Block\Adminhtml\Shipment
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        die;
        $this->_controller = 'adminhtml_shipment';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Shipments');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
