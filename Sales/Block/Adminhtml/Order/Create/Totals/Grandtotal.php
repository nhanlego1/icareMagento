<?php

/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 27/09/2016
 * Time: 11:30
 */
namespace Icare\Sales\Block\Adminhtml\Order\Create\Totals;
class Grandtotal extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\Grandtotal{
    public $_template = 'order/create/totals/grand.phtml';
    public function displayPriceAttribute($data){

    }
}