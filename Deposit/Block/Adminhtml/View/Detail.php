<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 10:09
 */

namespace Icare\Deposit\Block\Adminhtml\View;




use Magento\Backend\Block\Template;

class Detail extends Template{
    protected $_blockGroup = 'Icare_Deposit';
    public function _construct() {
        parent::_construct(); // TODO: Change the autogenerated stub
        $this->setId('icare_deposit_view');
    }
}