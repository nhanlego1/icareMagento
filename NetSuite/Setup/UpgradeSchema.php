<?php

namespace Icare\NetSuite\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
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
        $setup->startSetup();
        
        // add fulfillment_id column to sales_shipment       
        $tableShipment = $setup->getTable('sales_shipment');
        $setup->getConnection()->addColumn(
            $tableShipment,
            'fulfillment_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => true,
                'default' => null,
                'comment' => 'fulfillment internalId in NetSuite'
            ]
        );
        
        // add UNIQUE constraints for fulfillment_id
        $indexName = $setup->getConnection()->getIndexName($tableShipment, array('fulfillment_id'), AdapterInterface::INDEX_TYPE_UNIQUE);
        $setup->getConnection()->addIndex($tableShipment, $indexName, array('fulfillment_id'), AdapterInterface::INDEX_TYPE_UNIQUE);
        
        $setup->endSetup();
    }
}
