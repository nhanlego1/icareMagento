<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/6/16
 * Time: 12:01 AM
 */

namespace Icare\EventOrder\Observer;

use Icare\Cache\Model\IcareCache;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;


class LogHistoryBeforeOrderSave implements ObserverInterface
{

    /**
     * LogHistoryBeforeOrderSave constructor.
     * @param AdminSession $adminSession
     * @param \Icare\Cache\Model\IcareCache $icareCache
     */
    public function __construct(AdminSession $adminSession, \Icare\Cache\Model\IcareCache $icareCache )
    {
        $this->_adminSession = $adminSession;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_cache = $icareCache;

    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $event = $observer->getEvent();
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $event->getOrder();
        $oldOrder = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
        if ($order->getStatus() != $oldOrder->getStatus() || $order->getState() != $oldOrder->getState()) {
            $comment = "";
            if ($order->getStatus() != $oldOrder->getStatus()) {
                $comment .= 'Status change from ' . $oldOrder->getStatus() . ' to ' . $order->getStatus() . '||';
            }

            if ($order->getState() != $oldOrder->getState()) {
                $comment .= 'State change from ' . $oldOrder->getState() . ' to ' . $order->getState();
            }
            /** @var  \Magento\Sales\Model\Order\Status\History $orderHistory */
            $order->addStatusHistoryComment($comment)
                ->setIsCustomerNotified(false)->setIsVisibleOnFront(false);
        }

        $order->setData('oldOrder', $oldOrder);
    }


}