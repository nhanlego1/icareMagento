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

namespace Icare\Manageapi\Model;
use Magento\Framework\DataObject\IdentityInterface;


class Manageapi extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const ID = "id";

    /**#@+
     * Post's Statuses
     */


    const CONNECT_URL = "connect_url";
    const API_URL = "api_url";


    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'icare_manageapi';
    /**
     * @var string
     */
    protected $_cacheTag = 'icare_manageapi';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'icare_manageapi';


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
        $this->_init('Icare\Manageapi\Model\ResourceModel\Manageapi');
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

}