<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Gps\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function __construct(\Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressRepository)
    {
        $this->_addressRepository = $addressRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table = $installer->getTable('icare_gps');
            $installer->getConnection()->changeColumn(
                $table,
                'gps_id',
                'gps_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    'comment' => 'gps ID',
                    'nullable' => false,
                    'auto_increment' => true
                ]
            );
        }

        $installer->endSetup();
    }
}
