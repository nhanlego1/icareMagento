<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\User\Setup;

use Icare\User\Model\RoleConstant;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/* For get RoleType and UserType for create Role   */;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * RoleFactory
     *
     * @var roleFactory
     */
    private $_roleFactory;

    /**
     * RulesFactory
     *
     * @var rulesFactory
     */
    private $_rulesFactory;

    private $_roleCollectionFactory;
    /**
     * Init
     *
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Authorization\Model\RulesFactory $rulesFactory
     */
    public function __construct(
        \Magento\Authorization\Model\RoleFactory $roleFactory, /* Instance of Role*/
        \Magento\Authorization\Model\RulesFactory $rulesFactory, /* Instance of Rule */
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory

    )
    {
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableAdmins = $installer->getTable('admin_user');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'store_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Store Id'
            ]
        );

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'is_allowed_confirm_order',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Allowed confirm order'
            ]
        );
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('admin_user', ['store_id']),
            ['store_id']);

        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('admin_user', ['is_allowed_confirm_order']),
            ['is_allowed_confirm_order']);

        $role = $this->_getRoleByName(RoleConstant::GLOBAL_SUPPORT);
        if (!$role) {
            $role = $this->_roleFactory->create();
            $role->setName(RoleConstant::GLOBAL_SUPPORT)//Set Role Name Which you want to create
            ->setPid(0)//set parent role id of your role
            ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);

            $role->save();

        }

        $resource = ['Magento_Backend::all'];
        $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

        $installer->endSetup();
    }

    private function _getRoleByName($roleName) {
        $roleCollection = $this->_roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection
            ->addFieldToFilter('role_name', $roleName)
            ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
            ->getFirstItem();
        return $role->getId() ? $role : false;
    }
}
