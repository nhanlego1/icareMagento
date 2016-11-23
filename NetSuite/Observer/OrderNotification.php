<?php
namespace Icare\NetSuite\Observer;

use Icare\EventOrder\Plugin\OrderPlugin;
use Icare\NetSuite\Helper\Payload;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 *
 * @author Nam Pham
 *
 */
class OrderNotification implements ObserverInterface
{
    /**
     * @var \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface
     */
    private $_netsuiteQueue;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $_customerReg;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     *
     * @param \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface $netsuiteClient
     * @param \Magento\Customer\Model\CustomerRegistry $customerReg
     */
    public function __construct(
        \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface $netsuiteQueue,
        \Magento\Customer\Model\CustomerRegistry $customerReg,
        \Psr\Log\LoggerInterface $logger
        ) {
        $this->_netsuiteQueue = $netsuiteQueue;
        $this->_customerReg = $customerReg;
        $this->_logger = $logger;
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
        $oldOrder = $order->getData('oldOrder');
        if ($oldOrder != null && $oldOrder->getStatus() !== $order->getStatus() && $order->getStatus() == OrderPlugin::ORDER_CONFIRMED) {
            $this->postSalesOrder($order);
            $this->setNetsuiteStatus($order->getId());
        } else {
            $this->updateSalesOrderStatus($order);
        }
    }
    /**
     * Set Netsuite Status for and order
     * @param int $orderId
     * @return void
     */
    private function setNetsuiteStatus($orderId)
    {
        $resource = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $connection->update('sales_order', [
            'is_posted_netsuite' => 1
        ], [
            'entity_id = ?' => $orderId
        ]);
    }
    /**
     * post order data to NetSuite
     * @param \Magento\Sales\Model\Order $order
     */
    private function postSalesOrder($order)
    {
        $payload = Payload::convertOrderPayload($order);
        
        $customer = $order->getCustomer();
        if (!$customer) {
            $customer = $this->_customerReg->retrieve($order->getCustomerId());
        }

        $payload['customer'] = Payload::convertCustomerPayload($customer);
        $this->_logger->info(sprintf('Push Order To Netsuite[OrderNo=%s]', $order->getIncrementId()));
        $this->_netsuiteQueue->enqueue($order->getStore()->getWebsite()->getCode().'-sales_order', $payload);
    }
    
    /**
     * post order status to NetSuite
     * @param \Magento\Sales\Model\Order $order
     */
    private function updateSalesOrderStatus($order)
    {
        // ignored status because NetSuite does not need to know, or some status is duplicated with 
        // other notification i.e shipment
        $ignored = array(
            OrderPlugin::ORDER_SHIPPED,
            OrderPlugin::ORDER_DELIVERED,
        );
        if (array_search($order->getStatus(), $ignored) !== FALSE) {
            $payload = Payload::convertOrderPayload($order, false, false);
            $this->_netsuiteQueue->enqueue($order->getStore()->getWebsite()->getCode().'-sales_order', $payload);
        }
    }
}
