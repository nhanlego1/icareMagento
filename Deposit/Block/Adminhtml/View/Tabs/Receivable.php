<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 11:58
 */

namespace Icare\Deposit\Block\Adminhtml\View\Tabs;


use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Receivable extends \Magento\Framework\View\Element\Text\ListText implements TabInterface{
    
    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel() {
        // TODO: Implement getTabLabel() method.
        return __("Receivable History");
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle() {
        // TODO: Implement getTabTitle() method.
        return __("Receivable History");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab() {
        // TODO: Implement canShowTab() method.
        return TRUE;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden() {
        // TODO: Implement isHidden() method.
        return FALSE;
    }
}