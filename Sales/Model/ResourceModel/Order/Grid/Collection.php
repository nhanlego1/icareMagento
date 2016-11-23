<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Sales\Model\ResourceModel\Order\Grid;

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
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $this->_isStoreJoined = false;
    }

    protected function _initSelect() {
        $this->addFilterToMap('user_id', 'order_table.user_id');
        $this->addFilterToMap('increment_id', 'main_table.increment_id');
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('billing_name', 'main_table.billing_name');
        $this->addFilterToMap('shipping_name', 'main_table.shipping_name');
        $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
        $this->addFilterToMap('grand_total', 'main_table.grand_total');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('billing_address', 'main_table.billing_address');
        $this->addFilterToMap('shipping_address', 'main_table.shipping_address');
        $this->addFilterToMap('shipping_information', 'main_table.shipping_information');
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $this->addFilterToMap('customer_email', 'main_table.customer_email');
        $this->addFilterToMap('customer_group', 'main_table.customer_group');
        $this->addFilterToMap('subtotal', 'main_table.subtotal');
        $this->addFilterToMap('shipping_and_handling', 'main_table.shipping_and_handling');
        $this->addFilterToMap('customer_name', 'main_table.customer_name');
        $this->addFilterToMap('payment_method', 'main_table.payment_method');
        $this->addFilterToMap('total_refunded', 'main_table.total_refunded');
        $this->addFilterToMap('subtotal', 'main_table.subtotal');

        parent::_initSelect();


    }

    public function getMappedField($field) {
        return parent::_getMappedField($field);
    }


    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()
            ->joinLeft([
                'order_table' => $this->getTable('sales_order')
            ], 'main_table.entity_id = order_table.entity_id', [
                'order_table.user_id'
            ]);
        $user = $this->_authSession->getUser();
        if ($this->_checkSpecialUser($user)) {
            return $this;
        }
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $storeId = $user->getStoreId();
        if (!is_array($storeId)) {
            $storeId = [$storeId === null ? -1 : $storeId];
        }
        if (empty($storeId)) {
            return $this;
        }
        $this->addFieldToFilter("order_table.store_id", ['in' => $storeId]);

    }

    protected function _checkSpecialUser($user) {
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
            foreach($roles as $role) {
                if ($role->getRoleName() == RoleConstant::GLOBAL_SUPPORT || $role->getRoleName() == RoleConstant::ADMINISTRATORS) {
                    $isSpecialUser = true;
                    break;
                }
            }
        }
        return $isSpecialUser;
    }

}
