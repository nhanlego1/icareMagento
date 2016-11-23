<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\IcareOrderApi\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

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
        $tableAdmins = $installer->getTable('sales_order');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'user_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'user Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'loan_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Loan Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'icare_address_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'address Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'icare_address_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'address type'
            ]
        );
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('sales_order', ['loan_id']),
            ['loan_id']);
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('sales_order', ['user_id']),
            ['user_id']);
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('sales_order', ['icare_address_id']),
            ['icare_address_id']);
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('sales_order', ['icare_address_type']),
            ['icare_address_type']);

        //add table temp for order by saving account
        if(version_compare($context->getVersion(), '2.0.11') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('icare_saving_account'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
                    'ID'
                )
                ->addColumn('customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'customer id')
                ->addColumn('amount', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'amount')
                ->setComment('temp saving order');
            $installer->getConnection()->createTable($table);
        }
        
        $installer->endSetup();
    }
}
