<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Setup;

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
            ->newTable($installer->getTable('icare_deposit'))
            ->addColumn(
              'id',
              Table::TYPE_SMALLINT,
              null,
              ['identity' => true, 'nullable' => false, 'primary' => true],
              'ID'
            )
            ->addColumn('user_id', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'User ID')
            ->addColumn('customer_id', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Customer ID')
            ->addColumn('amount', Table::TYPE_DECIMAL, '12,4', ['nullable' => false, 'default' => '0.0000'], 'Amount')
            ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
            ->addColumn('is_deposit', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Deposit?')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->setComment('Tracking deposit');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}