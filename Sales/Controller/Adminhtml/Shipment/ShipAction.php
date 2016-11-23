<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/5/16
 * Time: 11:54 AM
 */

namespace Icare\Sales\Controller\Adminhtml\Shipment;


use Icare\EventOrder\Plugin\OrderPlugin;

class ShipAction extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\Index
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (isset($data['shipment_ids'])) {
                $shipmentObj = $this->_objectManager->create('\Magento\Sales\Model\Order\Shipment');
                $orderObj = $this->_objectManager->create('\Magento\Sales\Model\Order');
                $shipmentIds = explode(',', $data['shipment_ids']);
                foreach ($shipmentIds as $shipmentId) {
                    $shipment = $shipmentObj->load($shipmentId);
                    $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_PACKED);
                    $order = $orderObj->load($shipment->getOrderId());
                    if ($order) {
                        $order->setStatus(OrderPlugin::ORDER_SHIPPED);
                    }
                   $transaction = $this->_objectManager->get('Magento\Framework\DB\Transaction');
                   $transaction->addObject(
                       $shipment
                   )->addObject(
                       $order
                   )->save();
                }
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/shipment/readytoship');
    }

}