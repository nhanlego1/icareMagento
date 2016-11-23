<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/21/16
 * Time: 8:45 AM
 */

namespace Icare\Sales\Controller\Adminhtml\Shipment;


use Icare\EventOrder\Plugin\OrderPlugin;

class PrintAction extends \Magento\Sales\Controller\Adminhtml\Shipment\PrintAction
{
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $isPick = $this->getRequest()->getParam('isPick');
        if ($shipmentId) {
            // update status to shipped
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
            if ($shipment) {
                if ($isPick == 1) {
                    $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_PICKED);
                } else {
                    $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_PACKED);
                }
                $order = $shipment->getOrder();
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
        if (!$isPick) {
            return parent::execute();
        } else {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/shipment/readytoship');
        }
    }
}
