<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/4/16
 * Time: 3:55 PM
 */

namespace Icare\Sales\Model\ResourceModel\Grid;


class ICareCenterCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _initSelect()
    {
        $rs = parent::_initSelect();
        $this->getSelect()->join(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.entity_id = customer.entity_id', ['main_table.*', 'customer.icare_center_type']
        );
        return $rs;
    }
}