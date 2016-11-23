<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 10:43 AM
 */

namespace Icare\Gps\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
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
        $table = $installer->getConnection()
            ->newTable($installer->getTable('icare_gps'))
            ->addColumn(
                'gps_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
                'gps ID'
            )
            ->addColumn('lat', Table::TYPE_TEXT, 255, ['nullable' => false], 'Latitude')
            ->addColumn('long', Table::TYPE_TEXT, 255, ['nullable' => false], 'Longitude')
            ->addColumn('user_id', Table::TYPE_BIGINT, null, ['nullable' => false, 'default' => '0'], 'User ID')
            ->addColumn('order_id', Table::TYPE_BIGINT, null, ['nullable' => false, 'default' => '0'], 'Order ID')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->setComment('gps info');
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}