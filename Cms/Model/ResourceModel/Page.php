<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Icare\Cms\Model\ResourceModel;

use Magento\Framework\App\RequestInterface;

/**
 * Cms page mysql resource
 */
class Page extends \Magento\Cms\Model\ResourceModel\Page
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    )
    {
        parent::__construct($context, $storeManager, $dateTime, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
    }


    /**
     * Process page data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        if ($object->getVariable() && !empty($object->getVariable())) {
            if (is_array($object->getVariable())) {
                $variable = implode(',', $object->getVariable());
                $object->setVariable($variable);
            } else {
                $object->setVariable($object->getVariable());
            }
        }
        if ($object->getStores()) {
            $store_id = $object->getStores();
            $store_id = reset($store_id);
            $store = $om->create('Magento\Store\Model\Store')->load($store_id);
            $object->setWebsite($store->getWebsiteId());
        }
        return parent::_beforeSave($object);
    }

}
