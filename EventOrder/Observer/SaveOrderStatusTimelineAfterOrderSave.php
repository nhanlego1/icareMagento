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

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;


class SaveOrderStatusTimelineAfterOrderSave implements ObserverInterface
{

		public function __construct(
				\Magento\Framework\App\Helper\Context $context
		)
		{
				$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$this->_logger = $context->getLogger();
				$this->_icareHelper = $this->_objectManager->get('\Icare\Custom\Helper\ICareHelper');

		}

		/**
		 * Save order into registry to use it in the overloaded controller.
		 *
		 * @param \Magento\Framework\Event\Observer $observer
		 * @return $this
		 */
		public function execute(\Magento\Framework\Event\Observer $observer)
		{
		    try {
		        $event = $observer->getEvent();
		        /** @var  \Magento\Sales\Model\Order $order */
		        $order = $event->getOrder();
		        $this->_logger->info(sprintf('Insert Order Timeline[orderId=%s, status=%s]', $order->getId(), $order->getStatus()));
		        $this->_icareHelper->updateOrderTimeLine($order->getId(), $order->getStatus());
		    } catch (\Exception $ex) {
		        $this->_logger->error($ex);
		    }
		    
		}


		
		
}