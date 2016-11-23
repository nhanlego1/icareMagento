<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 1:58 PM
 */

namespace Icare\Shipping\Block\Adminhtml;


class Shipping extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_shipping';
        $this->_blockGroup = 'Icare_Shipping';
        $this->_headerText = __('Manage iCare Delivery Reason');
        parent::_construct();
        if ($this->_isAllowedAction('Icare_Shipping::save')) {
            $this->buttonList->update('add', 'label', __('Add New Delivery Reason'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}