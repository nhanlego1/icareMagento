<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */
namespace Icare\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Icare\User\Model\RoleConstant;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

/**
 * Class ProductDataProvider
 */
class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []

    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName,$collectionFactory, $addFieldStrategies,$addFilterStrategies, $meta, $data);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_roleCollectionFactory = $roleCollectionFactory;
    }

    public function addField($field, $alias = null)
    {
        parent::addField($field, $alias);
        $user = $this->_authSession->getUser();
        if (!$this->_checkSpecialUser($user) && !$this->_storeManager->isSingleStoreMode()) {
            $storeId = $user->getStoreId();
            if (!empty($storeId)) {
                $this->getCollection()->addStoreFilter($this->_storeManager->getStore($storeId));
            }
        }

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
