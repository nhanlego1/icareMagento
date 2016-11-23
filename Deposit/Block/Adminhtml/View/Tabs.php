<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 10:06
 */

namespace Icare\Deposit\Block\Adminhtml\View;


use Magento\Framework\View\Element\Template;

class Tabs extends \Magento\Backend\Block\Widget\Tabs{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('icare_deposit_view_tabs');
        $this->setDestElementId('icare_deposit_view');
        $this->setTitle(__('Deposit detail'));
    }
}