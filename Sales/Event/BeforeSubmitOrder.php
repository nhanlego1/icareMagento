<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 28/09/2016
 * Time: 14:03
 */

namespace Icare\Sales\Event;


use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class BeforeSubmitOrder implements \Magento\Framework\Event\ObserverInterface{


    public function beforeSubmit(Order $order, Quote $quote){
        $saving_account = $quote->getData('saving_account');
        $order->setData('saving_account', $saving_account?1:0);
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        // TODO: Implement execute() method.
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $auto_confirmation = $quote->getData('auto_confirmation');
        $saving_account = $quote->getData('saving_account');
        $saving_account_amount = $quote->getData('saving_account_amount');
        $icareAddressId = $quote->getData('icare_address_id');
        $icareAddressType = $quote->getData('icare_address_type');
        if($saving_account){
            $order->setData('saving_account', $saving_account);
            $order->setData('saving_account_amount', $saving_account_amount);
        }
        if($auto_confirmation){
            $order->setData('auto_confirmation',1);
        }
        else  $order->setData('auto_confirmation',0);

        if ($icareAddressId) {
            $order->setData('icare_address_id', $icareAddressId);
            $order->setData('icare_address_type', $icareAddressType);
        }

    }
}