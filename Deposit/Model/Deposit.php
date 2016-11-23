<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Model;
use Magento\Framework\DataObject\IdentityInterface;
use Icare\Deposit\Model\ResourceModel\Deposit as ResourceDeposit;


class Deposit extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const ID = "id";

    /**#@+
     * Statuses
     */
    const STATUS_YES = 1;
    const STATUS_NO = 0;

    const USER_ID = "user_id";
    const CUSTOMER_ID = "customer_id";
    const AMOUNT = "amount";
    const STATUS = "status";
    const IS_DEPOSIT = "is_deposit";
    const CREATION_TIME = "creation_time";

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'icare_deposit';
    /**
     * @var string
     */
    protected $_cacheTag = 'icare_deposit';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'icare_deposit';


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param ResourceDeposit $resource
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Icare\Deposit\Model\ResourceModel\Deposit $resource,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Icare\Deposit\Model\ResourceModel\Deposit');
    }


    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Is deposit
     *
     * @return bool|null
     */
    public function isDeposit()
    {
        return (bool) $this->getData(self::IS_DEPOSIT);
    }

    public function setIsDeposit($is_deposit)
    {
        return $this->setData(self::IS_DEPOSIT, $is_deposit);
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_YES => __('Yes'), self::STATUS_NO => __('No')];
    }

    /**
     * Load deposit by user
     *
     * @param   string $user_id
     */
    public function loadByUser($user_id)
    {
        $collection = $this->_getResource()->loadByUser($this, $user_id);
        return $collection;
    }

}