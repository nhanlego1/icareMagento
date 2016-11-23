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

namespace Icare\Sales\Setup;

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
            ->newTable($installer->getTable('icare_shipment_attachment'))
            ->addColumn(
                'attachment_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Attachment ID'
            )
            ->addColumn('shipment_id', Table::TYPE_INTEGER, null,['nullable' => false], 'Shipment Id')
            ->addColumn('attachment_url', Table::TYPE_TEXT, '5000', [], 'attachment url')
            ->addColumn('s3_key', Table::TYPE_TEXT, '5000', [], 's3 key')
            ->addIndex($installer->getIdxName('shipment_attachment_idx', ['shipment_id']), ['shipment_id']);
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}