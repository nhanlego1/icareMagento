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

namespace Icare\Custom\Setup;

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
        /**
         * 
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection('log_setup');
        
        $installer = $setup;
        $installer->startSetup();
        
        $table = $connection
            ->newTable($installer->getTable('request_log'))
            ->addColumn(
                'request_id',
                Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
                'request id'
            )
            ->addColumn('transaction_id', Table::TYPE_TEXT, 500, ['nullable' => true], 'Request Id from Mobile')
            ->addColumn('url', Table::TYPE_TEXT, 1000, ['nullable' => false], 'Url Request')
            ->addColumn('header', Table::TYPE_TEXT, 2000, ['nullable' => true], 'Header Request')
            ->addColumn('request_data', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => true], 'Request Data')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('method', Table::TYPE_TEXT, 10, ['nullable' => false], 'Http Method')
            ->setComment('request log');
            $connection->createTable($table);

        $installer->endSetup();
    }
}