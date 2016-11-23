<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 24/10/2016
 * Time: 15:01
 */

namespace Icare\MobileSecurity\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();


        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $tableAdmins = $installer->getTable('icare_mobile_security');
            $installer->getConnection()->modifyColumn(
                $tableAdmins,
                'pincode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                ],
                true
            );
        }
        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $tableAdmins = $installer->getTable('icare_mobile_security');
            $installer->getConnection()->modifyColumn(
                $tableAdmins,
                'device_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                ],
                true
            );
        }
        if (version_compare($context->getVersion(), '0.0.4') < 0) {
            // Check if the table already exists
            if ($installer->getConnection()->isTableExists('icare_access_token') != true) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('icare_access_token'))
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'nullable' => false, 'primary' => true],
                        'access ID'
                    )
                    ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Customer Id')
                    ->addColumn('device_id', Table::TYPE_TEXT, 100, ['nullable' => false, 'unique' => true], 'Device Id')
                    ->addColumn('access_token', Table::TYPE_TEXT, 255, ['nullable' => true], 'Access Token')
                    ->addColumn('is_lock', Table::TYPE_BOOLEAN, null, ['nullable' => true, 'default' => false], 'Status')
                    ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => true], 'Updated At')
                    ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => true], 'Created At');
                //->addIndex($installer->getIdxName('customer_entity', ['entity_if']), ['customer_id']);
                $installer->getConnection()->createTable($table);
            }
        }
        if (version_compare($context->getVersion(), '0.0.5') < 0) {
            $tableAdmins = $installer->getTable('icare_mobile_security');
            $installer->getConnection()->modifyColumn(
                $tableAdmins,
                'device_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'=>100,
                    'nullable' => true,
                ]
            );
            $installer->getConnection()->modifyColumn(
                $tableAdmins,
                'pincode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'=>100,
                    'nullable' => true,
                ]
            );
            $installer->getConnection()->addIndex($tableAdmins,'idx_mobi_security_customer_id','customer_id');
            $installer->getConnection()->addIndex($tableAdmins,'idx_mobi_security_device_id','device_id');
            $installer->getConnection()->addIndex($tableAdmins,'idx_mobi_security_pincode','pincode');
        }
        $installer->endSetup();
    }
}
