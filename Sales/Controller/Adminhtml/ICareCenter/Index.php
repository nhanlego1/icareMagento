<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/4/16
 * Time: 2:40 PM
 */

namespace Icare\Sales\Controller\Adminhtml\ICareCenter;


class Index extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customers list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Icare_Sales::catalog_icare_center');
        $resultPage->getConfig()->getTitle()->prepend(__('iCare Centers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('iCare Centers'), __('iCare Centers'));
        $resultPage->addBreadcrumb(__('Manage iCare Centers'), __('Manage iCare Centers'));

        $this->_getSession()->unsCustomerData();

        return $resultPage;
    }
}