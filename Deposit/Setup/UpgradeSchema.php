<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 13:41
 */

namespace Icare\Deposit\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('icare_deposit_payment')
        )->addColumn(
            'payment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn(
            'transaction_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [ 'nullable' => false],
            'Transaction amount'
        )->addColumn(
            'transaction_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [ 'nullable' => false],
            'Transaction Date'
        )->addColumn(
            'payment_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [ 'nullable' => false,],
            'Transaction Date'
        )->addColumn(
            'account',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [ 'nullable' => true ],
            'Account'
        )->addColumn(
            'check',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [ 'nullable' => true ],
            'Check'
        )->addColumn(
            'routing_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [ 'nullable' => true ],
            'Routing code'
        )->addColumn(
            'receipt',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [ 'nullable' => true ],
            'Receipt'
        )->addColumn(
            'bank',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [ 'nullable' => true ],
            'Bank'
        )->addColumn(
            'note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            [ 'nullable' => true ],
            'Note'
        )->addColumn(
            'attach_file',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            [ 'nullable' => true ],
            'Attach file'
        )->addColumn(
            'created_by',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [ 'nullable' => false ],
            'Created By'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            30,
            [ 'nullable' => false ],
            'User ID'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        );
        $installer->getConnection()->createTable($table);
    }
}