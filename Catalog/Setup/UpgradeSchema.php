<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Catalog\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableCPO = $setup->getTable('catalog_product_option');

        // add netsuite_key column
        $setup->getConnection()->addColumn(
            $tableCPO,
            'netsuite_key',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 100,
                'nullable' => false,
                'default' => '0',
                'comment' => 'NetSuite field ID'
            ]
        );


        // set netsuite_key for existing catalog_product_option
        $stmt = $setup->getConnection()->select()
            ->from($tableCPO, ['option_id', 'product_id'])
            ->query();

        $counter = 0;
        while ($row = $stmt->fetchObject()) {
            $bind = array(
                'netsuite_key' => 'default_key_' . (++$counter),
            );
            $setup->getConnection()->update($tableCPO, $bind, 'option_id = ' . $row->option_id);
        }

        // add constraints
        $setup->getConnection()->addIndex(
            $tableCPO,
            $setup->getIdxName('catalog_product_option', ['product_id', 'netsuite_key'], AdapterInterface::INDEX_TYPE_UNIQUE),
            ['product_id', 'netsuite_key'],
            AdapterInterface::INDEX_TYPE_UNIQUE);


        // Get tutorial_simplenews table
        $tableName = $setup->getTable('netsuite_catalog_product_option');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Product Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Store Id'
                )
                ->addColumn(
                    'option_type_id_array',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'option array'
                )
                ->addColumn(
                    'active',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => true],
                    'Active'
                )
                ->addColumn(
                    'on_update',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => false],
                    'Active'
                )
                ->addColumn(
                    'option_type_sku',
                    Table::TYPE_TEXT,
                    500,
                    ['nullable' => true, 'default' => ''],
                    'option type sku'
                )
                ->addIndex('product_id_store_id_option_type_id_array_idx', ['product_id', 'store_id', 'option_type_id_array'], AdapterInterface::INDEX_TYPE_INDEX)
                ->setComment('Netsuite Catalog Product Option Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);
        } else {
            if ($setup->getConnection()->tableColumnExists($tableName, 'on_update') != true) {
                $setup->getConnection()->addColumn($tableName, 'on_update',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => false],
                    'Active');
            }

            if ($setup->getConnection()->tableColumnExists($tableName, 'option_type_id_array') == true) {
                $setup->getConnection()->changeColumn($tableName, 'option_type_id_array', 'option_type_id_array',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                    ]);
            }

            $setup->getConnection()->addIndex(
                $tableName,
                'product_id_store_id_option_type_id_array_idx',
                ['product_id', 'store_id', 'option_type_id_array'],
                AdapterInterface::INDEX_TYPE_INDEX);

        }

        $tableName = $setup->getTable('netsuite_catalog_product_option');
        $setup->getConnection()->addColumn(
            $tableName,
            'option_type_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 500,
                'nullable' => true,
                'comment' => 'option type sku'
            ]
        );

        $setup->endSetup();
    }
}
