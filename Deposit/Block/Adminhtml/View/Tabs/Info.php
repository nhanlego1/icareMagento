<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 10:37
 */

namespace Icare\Deposit\Block\Adminhtml\View\Tabs;


use Magento\Framework\View\Element\Template;

class Info extends Template implements \Magento\Backend\Block\Widget\Tab\TabInterface{

    /**
     * @var string
     */
    protected $_template = 'view/tabs/info.phtml';
    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel() {
        return __("Deposit information");
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle() {
        return __("Deposit information");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab() {
       return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden() {
        return FALSE;
    }
}