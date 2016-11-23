<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Capture\Model\ResourceModel\Capture;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    const CACHE_TAG = 'customer_capture';
    const CUSTOMER_ID = 'customer_id';
    const UUID = 'uuid';
    const DEVICE_NAME = 'device_name';
    const OS_VERSION = 'os_version';
    const APP_VERSION = 'app_version';
    const LAT = 'lat';
    const LONG = 'long';
    const CREATE_AT = 'create_at';
    const ORDER_ID = 'order_id';
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Icare\Capture\Model\Capture', 'Icare\Capture\Model\ResourceModel\Capture');
        $this->_idFieldName = 'id';
    }

    /**
     * set data and get data
     */
    public function getCustomerId(){
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * set data and get data
     */
    public function getOrderId(){
        return $this->getData(self::ORDER_ID);
    }

}
