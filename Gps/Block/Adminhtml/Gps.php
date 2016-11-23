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

namespace Icare\Gps\Block\Adminhtml;


class Gps extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_gps';
        $this->_blockGroup = 'Icare_Gps';
        $this->_headerText = __('Manage Gps');
        parent::_construct();
        if ($this->_isAllowedAction('Icare_Gps::save')) {
            $this->buttonList->update('add', 'label', __('Add New Gps'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}