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


class Delivered extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\Index
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
        $resultPage->setActiveMenu('Icare_Sales::catalog_delivered')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Delivery'), __('Delivery'));
        $resultPage->getConfig()->getTitle()->prepend(__('Delivery'));
        return $resultPage;
    }
}