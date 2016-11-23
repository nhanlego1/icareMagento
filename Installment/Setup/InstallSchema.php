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

namespace Icare\Installment\Setup;

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
            ->newTable($installer->getTable('icare_installment_entity'))
            ->addColumn(
                'installment_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Installment ID'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Installment Title')
            ->addColumn('description', Table::TYPE_TEXT, '2M', [], 'Installment Description')
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Installment Active?')
            ->addColumn('number_of_repayment', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Number Of Prepayment')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->setComment('Installment Type');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('icare_installment_product_relation'))
            ->addColumn(
                'relation_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Relation ID'
            )
            ->addColumn('product_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Product Id')
            ->addColumn('installment_id', Table::TYPE_SMALLINT, null, ['nullable' => false], 'installment id')
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['nullable' => false], 'store id')
            ->addIndex($installer->getIdxName('installment_product_relation_idx', ['product_id']), ['product_id'])
            ->setComment('Installment Product Relation');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}