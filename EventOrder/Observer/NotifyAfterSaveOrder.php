<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/6/16
 * Time: 12:01 AM
 */

namespace Icare\EventOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Backend\Model\Auth\Session as AdminSession;


class NotifyAfterSaveOrder implements ObserverInterface
{
  /**
   *
   * @var \Magento\Framework\Registry
   */
  private $messageManager;

  public function __construct(
    \Magento\Framework\Message\ManagerInterface $messageManager,
    AdminSession $adminSession
  )
  {
    $this->messageManager = $messageManager;
    $this->_adminSession = $adminSession;
  }

  /**
   * Save order into registry to use it in the overloaded controller.
   *
   * @param \Magento\Framework\Event\Observer $observer
   * @return $this
   */
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    /* @var $order Order */
    $event = $observer->getEvent();
    $order = $event->getOrder();
    $user_id = $this->_adminSession->hasUser() ? (int)$this->_adminSession->getUser()->getId() : null;
    if($user_id && $order->getStatus() =='pending'){
      //@todo: update user id
      $order->setUserId($user_id);
      $order->save($order);
    }

  }
}