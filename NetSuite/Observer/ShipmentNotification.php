<?php

namespace Icare\NetSuite\Observer;

use Icare\NetSuite\Helper\Payload;
use Icare\Sales\Api\ShipmentInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Notify Netsuite to update Item Fulfillment status which should be synchronized with Shipment
 * @author Nam Pham
 *
 */
class ShipmentNotification implements ObserverInterface
{
    private $_netsuiteQueue;
    
    private $_resourceConnection;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface $netsuiteQueue)
    {
        $this->_netsuiteQueue = $netsuiteQueue;
        $this->_resourceConnection = $resourceConnection;
    }
    
    /**
     * Notify NetSuite if Shipment status is DELIVERED
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * 
         * @var \Magento\Sales\Model\Order\Shipment $shipment
         */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getShipmentStatus() == ShipmentInterface::STATUS_DELIVERED) {
            $this->notifyFinalizedShipment($shipment);
        }
    }
    
    /**
     * 
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     */
    private function notifyFinalizedShipment($shipment)
    {
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $this->_resourceConnection->getConnection();
        
        $attachments = $connection->select()
            ->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipment->getId())
            ->limit(10)
            ->query()
            ->fetchAll();
        
        $shipmentPL = Payload::convertShipmentPayload($shipment);
        foreach ($attachments as $attachment) {
            unset($attachment['shipment_id']);
            unset($attachment['s3_key']);
            $attachment['update_time'] = strtotime($attachment['update_time']);
            $shipmentPL['attachments'][] = $attachment;
        }
        $order = $shipment->getOrder();
        $payload = Payload::convertOrderPayload($order, FALSE, FALSE);
        $payload['shipments'][] = $shipmentPL;
        
        $this->_netsuiteQueue->enqueue($order->getStore()->getWebsite()->getCode().'-shipment', $payload);
    }
}