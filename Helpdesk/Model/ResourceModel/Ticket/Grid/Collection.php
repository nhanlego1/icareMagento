<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Helpdesk\Model\ResourceModel\Ticket\Grid;

use Icare\User\Model\RoleConstant;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
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
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_isStoreJoined = false;
        $this->_roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * check user and filter by store
     * @return $this
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $connection = $this->getConnection();
        $user = $this->_authSession->getUser();
        $storeId = $user->getStoreId();
        if (!$this->_checkSpecialUser($user) && !$this->_storeManager->isSingleStoreMode()) {
            if (!is_array($storeId)) {
                $storeId = [$storeId === null ? -1 : $storeId];
            }
            if (empty($storeId)) {
                return $this;
            }
            if (!$this->_isStoreJoined) {
                $this->getSelect()->distinct(
                    true
                )->join(
                    ['store' => $this->getTable('mb_ticket_store')],
                    'main_table.ticket_id = store.ticket_id',
                    []
                );
                //        ->group('main_table.ticket_id')
                $this->_isStoreJoined = true;
            }
            $this->addFieldToFilter("store.store_id", ['in' => $storeId]);
        }

    }

    /**
     * check user role
     * @param $user
     * @return bool
     */
    protected function _checkSpecialUser($user)
    {
        $roles = $user->getRoles();
        $isSpecialUser = false;
        if (!$roles) {
            $isSpecialUser = false;
        } else {
            $roles = $this->_roleCollectionFactory->create()
                ->addFieldToSelect('role_name')
                ->addFieldToFilter('role_id', ['in' => $roles])
                ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
                ->load();
            foreach ($roles as $role) {
                if ($role->getRoleName() == RoleConstant::GLOBAL_SUPPORT || $role->getRoleName() == RoleConstant::ADMINISTRATORS) {
                    $isSpecialUser = true;
                    break;
                }
            }
        }
        return $isSpecialUser;
    }
}
