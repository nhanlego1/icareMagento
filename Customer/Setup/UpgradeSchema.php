<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Customer\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressRepository,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    )
    {
        $this->_addressRepository = $addressRepository;
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
        $tableAdmins = $installer->getTable('customer_entity');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'telephone',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Telephone for customer'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'credit_limit',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Credit Limit'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'due_limit',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Due Limit'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'organization_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Organization Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'organization_name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Organization Name'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'employer_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Employer Id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'social_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'social id'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'social_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Social Type'
            ]
        );
        $installer->getConnection()->addIndex(
            $tableAdmins,
            $installer->getIdxName('customer_entity', ['telephone']),
            ['telephone']
        );
        $installer->getConnection()->addIndex(
            'customer_entity',
            'CUSTOMER_ENTITY_WEBSITE_ID_TELEPHONE_UNIQUE',
            ['website_id', 'telephone'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
        $addresses = $this->_addressRepository->create()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('telephone')
            ->addFieldToFilter('telephone', array('neq' => 'NULL'))->load();
        $connetion = $installer->getConnection();
        foreach ($addresses as $address) {
            try {
                $customer = $address->getCustomer();
                if ($customer) {
                    $connetion->update('customer_entity', ['telephone' => $address->getTelephone()],
                        $connetion->quoteInto('entity_id = ?', $customer->getId()));
                }
            } catch (\Zend_Db_Statement_Exception $ex) {
                print_r($ex->getMessage());
            }
        }

        //remove attribute from customer
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute('customer', 'client_id');

        if (version_compare($context->getVersion(), '2.3.34') < 0) {
            $tableAdmins = $installer->getTable('customer_entity');
            $installer->getConnection()->addColumn($tableAdmins,
                'client_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'client id of mifos'
                ]);
        }

        if (version_compare($context->getVersion(), '2.3.35') < 0) {
            $tableAdmins = $installer->getTable('customer_entity');
            $installer->getConnection()->addColumn($tableAdmins,
                'icare_center_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'ICare Center Type, 1: DC, 2: IC'
                ]);
        }
        
        if (version_compare($context->getVersion(), '2.3.36') < 0) {
            $tableAdmins = $installer->getTable('customer_address_entity');
            // add location_id field
            $installer->getConnection()->addColumn($tableAdmins,
                'location_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'NetSuite Location ID (internalid)'
                ]);
            // add UNIQUE constraints for location_id
            $indexName = $installer->getIdxName($tableAdmins, array('location_id'), AdapterInterface::INDEX_TYPE_UNIQUE);
            $installer->getConnection()->addIndex($tableAdmins, $indexName, array('location_id'), AdapterInterface::INDEX_TYPE_UNIQUE);
        }
        
        if (version_compare($context->getVersion(), '2.3.40') < 0) {
            $tableAdmins = $installer->getTable('wishlist_item');
            $installer->getConnection()->addColumn($tableAdmins,
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 10,
                    'comment' => 'Status of Pre-order'
                ]);
            $installer->getConnection()->addColumn($tableAdmins,
                'reason',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 2000,
                    'comment' => 'Reason when cancel pre-order'
                ]);
            $installer->getConnection()->addColumn($tableAdmins,
                'convert_to_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 32,
                    'comment' => 'convert to order'
                ]);
        }
        $installer->endSetup();
    }
}
