<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Custom\Setup;

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
        if (version_compare($context->getVersion(), "2.0.22") < 0) {
            /**
             *
             * @var \Magento\Framework\App\ResourceConnection $resource
             */
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection('log');
            $table = $connection
            ->newTable($connection->getTableName('request_log'))
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
        }
        
        if (version_compare($context->getVersion(), "2.0.23") < 0) {
            /**
             *
             * @var \Magento\Framework\App\ResourceConnection $resource
             */
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection('log');
            $table = $connection
            ->addColumn($connection->getTableName('request_log'),'result',
                [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                 'length' => Table::MAX_TEXT_SIZE,
                'comment' => 'result'
                    ]);
        }
        
        if (version_compare($context->getVersion(), "2.0.24") < 0) {
            /**
             *
             * @var \Magento\Framework\App\ResourceConnection $resource
             */
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection('log');
            $table = $connection
            ->addColumn($connection->getTableName('request_log'),'is_error',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment' => 'is_error'
                ]);
        }
        $installer->endSetup();
    }
}
