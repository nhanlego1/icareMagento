<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Helpdesk\Setup;

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
        $tableAdmins = $installer->getTable('mb_ticket');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'user_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'User Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'ticket_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'ticket_type'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'params',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'params'
            ]
        );
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('mb_ticket', ['user_id']),
            ['user_id']);
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('mb_ticket', ['ticket_type']),
            ['ticket_type']);
        

        $tableAttachment = $installer->getTable('mb_ticket_attachment');
        $installer->getConnection()->addColumn(
            $tableAttachment,
            's3_key',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'S3 key'
            ]
        );
        $installer->endSetup();
    }
}
