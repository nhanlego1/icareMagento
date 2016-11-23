<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 11:09 AM
 */

namespace Icare\Cms\Model\ResourceModel\Variables;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
    /**
     * @var string
     */
    protected $_idFieldName = 'variable_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Icare\Cms\Model\Variables', 'Icare\Cms\Model\ResourceModel\Variables');
    }


}