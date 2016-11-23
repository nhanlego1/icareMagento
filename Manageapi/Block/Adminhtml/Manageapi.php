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

namespace Icare\Manageapi\Block\Adminhtml;


class Manageapi extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_manageapi';
        $this->_blockGroup = 'Icare_Manageapi';
        $this->_headerText = __('Manage iCare Manageapi');
        parent::_construct();
        if ($this->_isAllowedAction('Icare_Manageapi::save')) {
            $this->buttonList->update('add', 'label', __('Add New Api Deployment'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}