<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/21/16
 * Time: 5:36 PM
 */

namespace Icare\Sales\Block\Adminhtml\Shipment;


class WaitToDeliver extends  \Magento\Backend\Block\Template
{
    public function getUpdateDeliveryUrl() {
        try {
            return $this->getUrl('sales/shipment/updatedelivery');
        } catch (\Exception $ex) {
            print_r($ex->getTraceAsString());
        }
    }

    public function getListReasonDelivery(){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['is_active'=>1];
        $select = $connection->select()->from(
            'icare_delivery_reason',
            ['id','reason','description']
        );
        $select->where('is_active = :is_active');
        $select->order('id ASC');
        $reasons = $connection->fetchAll($select, $bind);
        $data = [];
        if($reasons){
            foreach($reasons as $reason){
                $data[] = $reason['reason'];
            }
        }
        return $data;
    }
}