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


use Magento\Sales\Controller\Adminhtml\Shipment\Pdfshipments;

class MassPrintAction extends Pdfshipments
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (isset($data['shipment_ids'])) {
                $shipmentIds = explode(',', $data['shipment_ids']);
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_id', ['in' => $shipmentIds]);
                return $this->massAction($collection);

            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/shipment/readytoship');
    }

}