<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/20/16
 * Time: 4:58 PM
 */

namespace Icare\Sales\Model;


class DeliveredDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    protected function prepareUpdateUrl()
    {
        $this->addFilter(
            $this->filterBuilder->setField('shipment_status')->setValue([\Icare\Sales\Api\ShipmentInterface::STATUS_DELIVERED, \Icare\Sales\Api\ShipmentInterface::STATUS_FAILED])->setConditionType('in')->create()
        );

        $filters = $this->request->getParam('filters', null);
        if (isset($filters['icare_address_id']) && !empty($filters['icare_address_id'])) {
            $this->addFilter($this->filterBuilder->setField('icare_address_id')
                ->setValue($filters['icare_address_id'])
                ->setConditionType('eq')
                ->create());
        }
        return parent::prepareUpdateUrl(); // TODO: Change the autogenerated stub
    }

    

}
