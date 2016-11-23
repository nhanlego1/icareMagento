<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Model\ResourceModel;


class Deposit extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('icare_deposit', 'id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->_date->gmtDate());
        }
        return parent::_beforeSave($object);
    }

    /**
     * Load deposit by user
     *
     * @param \Icare\Deposit\Model\Deposit $deposit
     * @param string $user_id
     */
    public function loadByUser(\Icare\Deposit\Model\Deposit $deposit, $user_id)
    {
        $connection = $this->getConnection();
        $bind = ['user_id' => $user_id];
        $select = $connection->select()
            ->from($this->getTable('icare_deposit'), 'id'
        )->where(
            'user_id = :user_id'
        );

        $depositIds = $connection->fetchAll($select, $bind);
        if (!empty($depositIds)) {
            $collection = $deposit->getCollection();
            $collection->addIdFilter($depositIds);

            return $collection;
        }

        return NULL;
    }

}