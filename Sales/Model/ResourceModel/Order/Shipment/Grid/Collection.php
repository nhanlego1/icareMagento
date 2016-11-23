<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/5/16
 * Time: 11:14 AM
 */

namespace Icare\Sales\Model\ResourceModel\Order\Shipment\Grid;


class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _initSelect()
    {
        $this->addFilterToMap('user_id', 'order_table.user_id');
        $this->addFilterToMap('increment_id', 'main_table.increment_id');
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('billing_name', 'main_table.billing_name');
        $this->addFilterToMap('shipping_name', 'main_table.shipping_name');
        $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
        $this->addFilterToMap('grand_total', 'main_table.grand_total');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('billing_address', 'main_table.billing_address');
        $this->addFilterToMap('shipping_address', 'main_table.shipping_address');
        $this->addFilterToMap('shipping_information', 'main_table.shipping_information');
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $this->addFilterToMap('customer_email', 'main_table.customer_email');
        $this->addFilterToMap('customer_group', 'main_table.customer_group');
        $this->addFilterToMap('subtotal', 'main_table.subtotal');
        $this->addFilterToMap('shipping_and_handling', 'main_table.shipping_and_handling');
        $this->addFilterToMap('customer_name', 'main_table.customer_name');
        $this->addFilterToMap('payment_method', 'main_table.payment_method');
        $this->addFilterToMap('total_refunded', 'main_table.total_refunded');
        $this->addFilterToMap('subtotal', 'main_table.subtotal');
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $authSession = $om->get('Magento\Backend\Model\Auth\Session');
        $storeId = $authSession->getUser()->getStoreId();
        if (empty($storeId)) {
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        } else {
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);
        }
        $rs = parent::_initSelect();
        $this->getSelect()->join(
            ['sales_order' => $this->getTable('sales_order')],
            'main_table.order_id = sales_order.entity_id', ['main_table.*', 'sales_order.icare_address_type', 'sales_order.icare_address_id', 'sales_order.shipping_method']
        )->join(
            ['customer_address_entity' => $this->getTable('customer_address_entity')],
            'sales_order.icare_address_id = customer_address_entity.entity_id',
            ['customer_address_entity.firstname as location']
        )->where('main_table.store_id = ?', $store->getStoreId());
        return $rs;
    }
    
    public function getMappedField($field) {
        return parent::_getMappedField($field);
    }
}