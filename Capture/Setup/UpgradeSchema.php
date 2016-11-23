<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Capture\Setup;


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
        $tableAdmins = $installer->getTable('customer_capture');

        $installer->getConnection()->modifyColumn(
            $tableAdmins,
            'customer_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'customer Id'
            ]
        );
        
        if (version_compare($context->getVersion(), '2.0.7')) {
            $table = $setup->getTable('customer_capture');
            $setup->getConnection()->addColumn(
                $table,
                'is_icaremember_confirm',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'identify icaremember confirm or field sale confirm'
                ]
                );
        }

        $installer->endSetup();
    }

}
