<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\Create\Store;


use Magento\Backend\App\Action;
use Magento\Framework\App\RequestInterface as RequestInterface;
use Magento\Framework\App\ObjectManager;
/**
 * Adminhtml sales order create select store block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends \Magento\Sales\Block\Adminhtml\Order\Create\Store\Select
{


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sc_store_select');
    }

    /**
     * Get current customer
     */
    public function getOrderCustomer(){
        $data = [];
        $object = ObjectManager::getInstance();
        $session =  $object->get('Magento\Backend\Model\Session\Quote');
        $customerId = $session->getCustomerId();
        $urlObject = $object->get('Magento\Framework\UrlInterface');
        $currentUrl = $urlObject->getCurrentUrl();
        $data['url'] = $currentUrl;
        if($customerId){
            $customer = $object->get('Magento\Customer\Model\Customer')->load($customerId);
            $store = $customer->getStore();
            $website = $store->getWebsite();
            $data['website'] = $website;
        }

        return $data;
    }


}
