<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 18/10/2016
 * Time: 09:11
 */
namespace Icare\User\Helper;

use Icare\User\Model\RoleConstant;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

class Role extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Role collection
     *
     * @var \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory
     */
    protected $_roleCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
    ) {
        $this->_resource = $resource;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Returns table name
     *
     * @param string|array $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->_resource->getTableName($name);
    }

    /**
     * Check user role
     * \Magento\User\Model\User $user
     * @return bool
     */
    public function checkSpecialUser($user)
    {
        if (!$user->getId()) {
            return false;
        }

        $table = $this->getTable('authorization_role');
        $connection = $this->_resource->getConnection();
        $role_names = [RoleConstant::GLOBAL_SUPPORT, RoleConstant::ADMINISTRATORS];

        $select = $connection->select()->from(
            $table,
            []
        )->joinLeft(
            ['ar' => $table],
            "(ar.role_id = {$table}.parent_id and ar.role_type = '" . RoleGroup::ROLE_TYPE . "')",
            ['role_id']
        )->where(
            "{$table}.user_id = :user_id"
        )->where(
            "ar.role_name IN (?)", $role_names
        );

        $binds = ['user_id' => (int)$user->getId()];
        $roles = $connection->fetchCol($select, $binds);

        if ($roles) {
            return true;
        }

        return false;
    }
}