<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Sales\Setup;


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

        // Get tutorial_simplenews table
        $tableName = $installer->getTable('sales_order_timeline');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
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
                    'order_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Order Id'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Status'
                )
                ->addColumn(
                    'updated_date',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Updated'
                )
                ->addColumn(
                    'created_date',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Created'
                )
                ->setComment('News Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.27') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('icare_shipment_attachment'))
                ->addColumn(
                    'attachment_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Attachment ID'
                )
                ->addColumn('shipment_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Shipment Id')
                ->addColumn('attachment_url', Table::TYPE_TEXT, '5000', [], 'attachment url')
                ->addColumn('s3_key', Table::TYPE_TEXT, '5000', [], 's3 key')
                ->addIndex($installer->getIdxName('shipment_attachment_idx', ['shipment_id']), ['shipment_id']);
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.28') < 0) {
            $table = $installer->getTable('icare_shipment_attachment');
            $installer->getConnection()->addColumn(
                $table,
                'delivery_failed_reason',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'delivery failed reason'
                ]
            );
        }


        $table = $installer->getTable('icare_shipment_attachment');
        $installer->getConnection()->addColumn(
            $table,
            'update_time',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                'comment' => 'delivery time'
            ]
        );
        $installer->getConnection()->addColumn(
            $table,
            'reason_detail',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'reason detail'
            ]
        );

        if (version_compare($context->getVersion(), '2.0.31') < 0) {
            $table = $installer->getTable('sales_order');
            $installer->getConnection()->addColumn(
                $table,
                'saving_account',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Saving account using'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'saving_account_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Saving account amount using'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.32') < 0) {
            $table = $installer->getTable('sales_order');
            $installer->getConnection()->addColumn(
                $table,
                'saving_account_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Saving account amount'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.33') < 0) {
            $table = $installer->getTable('sales_order_item');
            $installer->getConnection()->addColumn(
                $table,
                'tax_rate_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'tax rate for sale order item'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.36') < 0) {
            $table = $installer->getTable('icare_shipment_attachment');
            $installer->getConnection()->changeColumn(
                $table,
                'attachment_id',
                'attachment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Attachment ID'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.38') < 0) {
            $table = $installer->getTable('sales_order');
            $installer->getConnection()->addColumn(
                $table,
                'auto_confirmation',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Auto confirmation'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '2.0.39') < 0) {
            $table = $installer->getTable('icare_shipment_attachment');
            $installer->getConnection()->changeColumn(
                $table,
                'attachment_id',
                'attachment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Attachment ID',
                    'auto_increment' => true
                ]
                );
        }


        if (version_compare($context->getVersion(), '2.0.40') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('icare_preorder_tracking'))
                ->addColumn(
                    'track_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Tracking ID'
                )
                ->addColumn('user_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'user Id')
                ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'customer Id')
                ->addColumn('product_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'product Id')
                ->addColumn('reason', Table::TYPE_TEXT, '2M', [], 'reason')
                ->addIndex($installer->getIdxName('preorder_tracking_idx', ['track_id']), ['track_id']);
            $installer->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '2.0.42') < 0) {
            $table = $installer->getTable('sales_order');
            $installer->getConnection()->modifyColumn(
                $table,
                'saving_account_amount',
                [
                    'type'=>\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'=>'12,4',
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Saving account amount'
                ]
            );
        }
        $this->update_2_0_43($setup, $context);
        $this->update_2_0_44($setup, $context);

        if (version_compare($context->getVersion(), '2.0.45') < 0) {
            $table = $installer->getTable('sales_order');
            $installer->getConnection()->addColumn(
                $table,
                'saving_transaction_id',
                [
                    'type'=>\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Saving Transaction ID'
                ]
            );
        }
        $installer->endSetup();
    }

    /**
     * Upgrades DB schema to version 2.0.43
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    function update_2_0_43(SchemaSetupInterface $setup, ModuleContextInterface $context) 
    {
        if (\version_compare($context->getVersion(), '2.0.43') >= 0) return;
        
        // icare_sales_order_aggregated_created
        $table = $setup->getConnection()->createTableByDdl(
            $setup->getTable('sales_order_aggregated_created'),
            $setup->getTable('icare_sales_order_aggregated_created')
        )->addIndex(
            $setup->getIdxName(
                'icare_sales_order_aggregated_created',
                ['user_id', 'period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['user_id', 'period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'User ID of the sales agent'
        )->addForeignKey(
            $setup->getFkName('icare_sales_order_aggregated_created', 'store_id', 'admin_user', 'user_id'),
            'user_id',
            $setup->getTable('admin_user'),
            'user_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        
        $setup->getConnection()->createTable($table);
        
        // remove old unique index
        $setup->getConnection()->dropIndex(
            $setup->getTable('icare_sales_order_aggregated_created'), 
            $setup->getIdxName(
                'icare_sales_order_aggregated_created',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ));
        
        // icare_sales_order_aggregated_updated
        $setup->getConnection()->createTable(
            $setup->getConnection()->createTableByDdl(
                $setup->getTable('icare_sales_order_aggregated_created'),
                $setup->getTable('icare_sales_order_aggregated_updated')
            )
        );
    }
    /**
     * Upgrades DB schema to version 2.0.44
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private  function update_2_0_44(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (\version_compare($context->getVersion(), '2.0.44') >= 0) {
            return;
        }

        $table = $setup->getTable('sales_order');
        $setup->getConnection()->addColumn(
            $table,
            'is_posted_netsuite',
            [
                'type' =>  Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Posting to netsuite status'
            ]
        );


    }
}
