<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Installment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function __construct(\Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressRepository)
    {
        $this->_addressRepository = $addressRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableAdmins = $installer->getTable('sales_order_item');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'installment_number_of_repayment',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Number Of Repayment'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'installment_information',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 5000,
                'nullable' => true,
                'comment' => 'Installment Information'
            ]
        );

        $installer->endSetup();
    }
}
