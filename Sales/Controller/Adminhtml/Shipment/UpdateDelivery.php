<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/21/16
 * Time: 5:29 PM
 */

namespace Icare\Sales\Controller\Adminhtml\Shipment;


use Icare\EventOrder\Plugin\OrderPlugin;

class UpdateDelivery extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\Index
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Icare\Custom\Helper\S3Helper $s3Helper

    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_s3Helper = $s3Helper;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $shipmentId = $this->getRequest()->getParam('shipmentId');
            if(!is_numeric($shipmentId) && strpos($shipmentId,'?')){
                $id = explode('?',$shipmentId);
                $shipmentId = $id[0];
            }
            $isDeliverySuccess = $data['is_delivery_success'];
            if ($isDeliverySuccess == 1) {
                if (isset($_FILES['delivery_upload_file']['name']) && is_array($_FILES['delivery_upload_file']['name'])) {
                    foreach($_FILES['delivery_upload_file']['name'] as $index => $fileName) {
                        $tmpFile = $_FILES['delivery_upload_file']['tmp_name'][$index];
                        if ($this->_s3Helper->isS3Usage()){
                            $folder = $this->getAttachmentFolder($shipmentId);
                            if (is_uploaded_file($tmpFile)) {
                                if($fileName != '') {
                                    $url = $this->_s3Helper->uploadFile($folder . '/' . $fileName, fopen($tmpFile, 'rb'));
                                    unlink($tmpFile);
                                    $this->saveShipmentAttachment($shipmentId, $url, $folder . '/' . $fileName);
                                }

                            }
                        }
                    }
                }
                // update status to shipped
                $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
                if ($shipment) {
                    $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_DELIVERED);
                    $order = $shipment->getOrder();
                    $order->setStatus(OrderPlugin::ORDER_DELIVERED);
                    $transaction = $this->_objectManager->get('Magento\Framework\DB\Transaction');
                    $transaction->addObject(
                        $shipment
                    )->addObject(
                        $order
                    )->save();
                }
            } else {
                if($data['reason_detail']){
                    $detail = $data['reason_detail'];
                }else{
                    $detail = $data['delivery_failed_reason'];
                }
                $reason = $data['delivery_failed_reason'];

                $this->saveShipmentReason($shipmentId, $reason, $detail);
                // update status to shipped
                $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
                if ($shipment) {
                    $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_FAILED);
                    $order = $shipment->getOrder();
                    $order->setStatus(OrderPlugin::ORDER_DELIVERY_FAILED);
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
        return $resultRedirect->setPath('sales/shipment/waitfordeliver');

    }

    protected function getAttachmentFolder($shipmentId) {
        return '/shipment/delivered/' . $shipmentId .'/' . time();
    }

    protected function saveShipmentAttachment($shipmentId,  $url, $key) {
        $resource  = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $insertValues = [
            'shipment_id' => $shipmentId,
            'attachment_url' => $url,
            's3_key' => $key
        ];
        $connection->insert('icare_shipment_attachment',
            $insertValues);
    }


    protected function saveShipmentReason($shipmentId,  $reason, $detail = '') {
        $resource  = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $insertValues = [
            'shipment_id' => $shipmentId,
            'delivery_failed_reason' => $reason,
            'reason_detail' => $detail
        ];
        $connection->insert('icare_shipment_attachment',
            $insertValues);
    }
}
