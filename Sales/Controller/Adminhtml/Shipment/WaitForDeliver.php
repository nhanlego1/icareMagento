<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/20/16
 * Time: 2:49 PM
 */

namespace Icare\Sales\Controller\Adminhtml\Shipment;


class WaitForDeliver extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\Index
{
    /**
     * Shipments grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Icare_Sales::catalog_wait_for_deliver')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Shipment Tracking'), __('Shipment Tracking'));
        $resultPage->getConfig()->getTitle()->prepend(__('Shipment Tracking'));
        return $resultPage;
    }
}