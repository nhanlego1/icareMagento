<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */


namespace Icare\Deposit\Model\ResourceModel\Deposit;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Construct
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init(
            'Icare\Deposit\Model\Deposit',
            'Icare\Deposit\Model\ResourceModel\Deposit'
        );
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource
        );
        $this->storeManager = $storeManager;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $depositId
     * @param boolean $exclude
     * @return $this
     */
    public function addIdFilter($depositId, $exclude = false)
    {
        if (empty($depositId)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($depositId)) {
            if (!empty($depositId)) {
                if ($exclude) {
                    $condition = ['nin' => $depositId];
                } else {
                    $condition = ['in' => $depositId];
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = ['neq' => $depositId];
            } else {
                $condition = $depositId;
            }
        }
        $this->addFieldToFilter('id', $condition);
        return $this;
    }
}