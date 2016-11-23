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

namespace Icare\Cms\Block\Adminhtml;


class Variables extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_variables';
        $this->_blockGroup = 'Icare_Cms';
        $this->_headerText = __('Manage iCare Variables');
        parent::_construct();
        if ($this->_isAllowedAction('Magento_Cms::save')) {
            $this->buttonList->update('add', 'label', __('Add Variables'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}