<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\App\ObjectManager;
/**
 * Adminhtml sales orders creation process controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Index extends \Magento\Sales\Controller\Adminhtml\Order\Create\Index
{

    public function execute()
    {
        parent::execute();
        $this->_request->getParam('customer_id');
        $object = ObjectManager::getInstance();
        if($this->_getSession()->getCustomerId() > 0){
            $customerId = $this->_getSession()->getCustomerId();
            $customer = $object->get('Magento\Customer\Model\Customer')->load($customerId);
            $store = $customer->getStore();
            $website = $store->getWebsite();

            $stores = array();
            foreach ($website->getGroups() as $group) {
                $stores[] = $group->getStores();
            }

            if (count($stores) <= 1) {
                $this->_getSession()->setStoreId((int)$store->getId());
            }

        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Order'));
        return $resultPage;
    }

  
}
