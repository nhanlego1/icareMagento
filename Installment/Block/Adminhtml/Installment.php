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

namespace Icare\Installment\Block\Adminhtml;


class Installment extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_installment';
        $this->_blockGroup = 'Icare_Installment';
        $this->_headerText = __('Manage iCare Installment');
        parent::_construct();
        if ($this->_isAllowedAction('Icare_Installment::save')) {
            $this->buttonList->update('add', 'label', __('Add New Installment'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}