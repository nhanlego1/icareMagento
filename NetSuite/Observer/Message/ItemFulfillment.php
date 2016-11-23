<?php
namespace Icare\NetSuite\Observer\Message;

use Icare\NetSuite\Helper\Payload;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Observe NetSuite message about item fulfillment
 * @author Nam Pham
 *
 */
class ItemFulfillment implements ObserverInterface
{
    protected $_orderFactory;
    
    protected $_orderShipment;
    
    protected $_queue;
    
    protected $_logger;

    
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Icare\IcareOrderApi\Model\OrderShipment $orderShipment,
        \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface $queue,
        \Psr\Log\LoggerInterface $logger
        ) {
        $this->_orderFactory = $orderFactory;
        $this->_orderShipment = $orderShipment;
        $this->_queue = $queue;
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
        $event = $observer->getEvent();
        $payload = $event->getDataByKey('payload');
        $order = $this->_orderFactory->create()->loadByIncrementId($payload->order_id);
        
        $logger = $event->getDataByKey('logger') ? $event->getDataByKey('logger') : $this->_logger;
        if (! $order->getId()) {
            $logger->error(sprintf('Invalid Order Increment ID: %s' , $payload->order_id));
            return;
        }
        
        /**
         *
         * @var \Icare\IcareOrderApi\Model\OrderShipment $orderShipment
         */
        $orderShipment = \Magento\Framework\App\ObjectManager::getInstance()->create('Icare\IcareOrderApi\Model\OrderShipment');
        try {
            $shipment = $orderShipment->generateShipment($order, (array)$payload);
            $logger->info(sprintf('created shipment %s for order %s', $shipment->getIncrementId(),  $order->getIncrementId()));
            
            $payload = \Icare\NetSuite\Helper\Payload::convertShipmentPayload($shipment);
            $this->_queue->enqueue($order->getStore()->getWebsite()->getCode().'-shipment', $payload);
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $logger->error(sprintf("data error creating shipment for order %s: %s", $order->getIncrementId(), $ex->getMessage()));
        }
        catch (\Magento\Framework\Exception\ValidatorException $ex) {
            $logger->error(sprintf("failed to create shipment for order %s: %s\n%s", $order->getIncrementId(), $ex->getMessage(), $ex->getTraceAsString()));
        }
    }
}
