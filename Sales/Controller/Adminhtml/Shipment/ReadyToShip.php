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

class ReadyToShip extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\Index
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context,$resultPageFactory);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Shipments grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $data = $this->getRequest()->getPostValue();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Icare_Sales::catalog_ready_to_ship')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Ready To Ship'), __('Ready To Ship'));
        $resultPage->getConfig()->getTitle()->prepend(__('Ready To Ship'));
        return $resultPage;
    }
}