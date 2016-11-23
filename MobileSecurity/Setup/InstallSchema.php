<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */

namespace Icare\MobileSecurity\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    const TABLE_NAME = 'icare_mobile_security';
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        // Get tutorial_simplenews table
        $tableName = $installer->getTable(self::TABLE_NAME);
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable($tableName))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Record ID'
                )
                ->addColumn('customer_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Customer Id')
                ->addColumn('device_id', Table::TYPE_TEXT, 100,['nullable' => false,'unique'=>true], 'Device Id')
                ->addColumn('pincode', Table::TYPE_TEXT, 100,['nullable' => true], 'Pin Code')
                ->addColumn('status', Table::TYPE_BOOLEAN, null, ['nullable' => true,'default'=>true], 'Status')
                ->addColumn('device_info', Table::TYPE_TEXT, 255, ['nullable' => true], 'Device Info')
                ->addColumn('last_login', Table::TYPE_DATETIME, null, ['nullable' => true], 'Last Login')
                ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => true], 'Updated At')
                ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => true], 'Created At');
                //->addIndex($installer->getIdxName('customer_entity', ['entity_if']), ['customer_id']);
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}