<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Cms\Setup;


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
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.38') < 0) {
            $tableAdmins = $installer->getTable('cms_page');
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => TRUE,
                    'default' => 0,
                    'comment' => 'Category'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'website',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => TRUE,
                    'default' => 0,
                    'comment' => 'Website'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => TRUE,
                    'default' => '',
                    'comment' => 'Type'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'variable',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => TRUE,
                    'default' => '',
                    'comment' => 'variable'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'like',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => TRUE,
                    'default' => 0,
                    'comment' => 'like'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'unlike',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => TRUE,
                    'default' => 0,
                    'comment' => 'unlike'
                ]
            );
            $installer->getConnection()->addIndex(
                $tableAdmins,
                $installer->getIdxName('cms_page', ['category']),
                ['category']);
            $installer->getConnection()->addIndex(
                $tableAdmins,
                $installer->getIdxName('cms_page', ['website']),
                ['website']);
            $installer->getConnection()->addIndex(
                $tableAdmins,
                $installer->getIdxName('cms_page', ['variable']),
                ['variable']);
            $installer->getConnection()->addIndex(
                $tableAdmins,
                $installer->getIdxName('cms_page', ['like']),
                ['like']);
            $installer->getConnection()->addIndex(
                $tableAdmins,
                $installer->getIdxName('cms_page', ['unlike']),
                ['unlike']);
        }

        if (version_compare($context->getVersion(), '2.0.40') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('icare_customer_page_rating'))
                ->addColumn(
                    'id',
                    Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn('page_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Page Id')
                ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Customer Id')
                ->addColumn('rating', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Rating Score')
                ->addIndex($installer->getIdxName('customer_page_rating_idx', ['page_id']), ['page_id']);
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.41') < 0) {
            $table = $installer->getTable('icare_customer_page_rating');
            $installer->getConnection()->addColumn(
                $table,
                'type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'comment' => 'Type'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'entity_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    'nullable' => false,
                    'comment' => 'Entity ID'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'data',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Data'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'creation_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'Creation Time'
                ]
            );
        }

        $installer->endSetup();
    }

}
