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

namespace Icare\Customer\Model\ResourceModel\Grid;

class CustomerGridCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Role helper
     *
     * @var \Icare\User\Helper\Role
     */
    protected $_roleHelper;

    /**
     * Auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Icare\User\Helper\Role $roleHelper
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Icare\User\Helper\Role $roleHelper
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_roleHelper = $roleHelper;
    }

    protected function _initSelect()
    {
        $rs = parent::_initSelect();
        return $rs;
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
        $this->addFilter('icare_center_type', ['null' => true], 'public');
        $user = $this->_authSession->getUser();
        $storeId = $user->getStoreId();
        if (!$this->_roleHelper->checkSpecialUser($user) && !$this->_storeManager->isSingleStoreMode()) {
            if (!is_array($storeId)) {
                $storeId = [$storeId === null ? -1 : $storeId];
            }
            if (empty($storeId)) {
                return $this;
            }
            $this->addFieldToFilter("store_id", ['in' => $storeId]);
        }
    }
}