<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 10:56 AM
 */

namespace Icare\Shipping\Model;
use Magento\Framework\DataObject\IdentityInterface;


class Shipping extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const ID = "id";

    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const REASON = "reason";
    const DESCRIPTION = "description";
    const IS_ACTIVE = "is_active";
    const CREATION_TIME = "creation_time";
    const UPDATE_TIME = "update_time";

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'icare_shipping';
    /**
     * @var string
     */
    protected $_cacheTag = 'icare_shipping';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'icare_shipping';


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
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
        $this->_init('Icare\Shipping\Model\ResourceModel\Shipping');
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
     * Is active
     *
     * @return bool|null
     */
    public function isActive()
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive($is_active)
    {
        return $this->setData(self::IS_ACTIVE, $is_active);
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

}