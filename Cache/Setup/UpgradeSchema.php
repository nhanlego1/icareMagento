<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
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
    public function __construct(
        //\Icare\Cache\Model\CustomerCache $customerCache
    )
    {
        //$this->_customerCache = $customerCache;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //$this->_customerCache->refresh();
        $installer->endSetup();
    }

}
