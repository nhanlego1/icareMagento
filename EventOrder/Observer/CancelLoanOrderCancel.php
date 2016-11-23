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
use Icare\Mifos\Helper\Mifos;

class CancelLoanOrderCancel implements ObserverInterface
{

    /**
     *
     * @var \Magento\Framework\Event\ManagerInterface $_eventManager
     */
    private $_eventManager;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Icare\Custom\Helper\ICareHelper $icareHelper)
    {
        $this->_eventManager = $context->getEventManager();
        $this->_icareHelper = $icareHelper;
        $this->_logger = $context->getLogger();
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
            /* @var $order Order */
            $event = $observer->getEvent();
            $order = $event->getOrder();
            if ($order->getLoanId() > 0) {
                Mifos::create()->cancelLoan($order->getLoanId(), $order->getId());
            }

            // Return money to saving if use
            if (!empty($order->getSavingAccountAmount()) && $order->getSavingAccountAmount() > 0) {
                Mifos::create()->addDeposit($order->getCustomerId(), $order->getSavingAccountAmount(), $order->getId());
            }
            
            $this->_icareHelper->updateOrderTimeLine($order->getId(), $order->getStatus());
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
        }
        
    }
}