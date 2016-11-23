<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Customer\Setup;

use Icare\Variable\Model\Variable;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavAttribute;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $this->upgrade_2_3_36($setup, $context, $customerSetup);
        $this->upgrade_2_3_37($setup, $context, $customerSetup);
        $this->upgrade_2_3_38($setup, $context, $customerSetup);
        $this->upgrade_2_3_39($setup, $context, $customerSetup);



        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }
    
    /**
     * upgrade data
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param CustomerSetup $customerSetup
     * @since 2.3.36
     */
    protected function upgrade_2_3_36(ModuleDataSetupInterface $setup, ModuleContextInterface $context, CustomerSetup $customerSetup)
    {
        if (version_compare($context->getVersion(), '2.3.36', '>=')) return;
        $entityAttributes = [
            'customer' => [
                'icare_center_type' => [
                    'type' => 'static',
                    'label' => 'iCare Center Type',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ],
            'customer_address' => [
                'location_id' => [
                    'type' => 'static',
                    'label' => 'NS Location ID',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ],
            ],
        ];
    
        foreach ($entityAttributes as $entity => $attributes) {
            foreach ($attributes as $attr => $values) {
                $customerSetup->addAttribute($entity, $attr, $values);
            }
        }
    }
    
    /**
     * upgrade data
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param CustomerSetup $customerSetup
     * @since 2.3.37
     */
    protected function upgrade_2_3_37(ModuleDataSetupInterface $setup, ModuleContextInterface $context, CustomerSetup $customerSetup) 
    {
        if (version_compare($context->getVersion(), '2.3.37', '>=')) return;
        $entityAttributes = [
            'customer' => [
                'is_active' => [
                    'type' => 'static',
                    'label' => 'Is Active',
                    'input' => 'boolean',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ],
            'customer_address' => [
                'is_active' => [
                    'type' => 'static',
                    'label' => 'Is Active',
                    'input' => 'boolean',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ],
            ],
        ];
        
        foreach ($entityAttributes as $entity => $attributes) {
            foreach ($attributes as $attr => $values) {
                $customerSetup->addAttribute($entity, $attr, $values);
            }
        }



        
        $setup->getConnection()->insert($setup->getTable('variable'), [
            'code' => 'paymentTypeId',
            'name' => 'Payment Type ID'
        ]);
        $om = ObjectManager::getInstance();
        /**@var \Magento\Variable\Model\Variable $var **/
        $var = $om->create('Magento\Variable\Model\Variable');

        $var->loadByCode('paymentTypeId');
        $setup->getConnection()->insert($setup->getTable('variable_value'), [
            'variable_id' => $var->getData('variable_id'),
            'store_id' =>0,
            'plain_value' => 1
        ]);

        
    }

    /**
     * upgrade_2_3_38
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    public function upgrade_2_3_38(ModuleDataSetupInterface $setup, ModuleContextInterface $context, CustomerSetup $customerSetup){
        if (version_compare($context->getVersion(), '2.3.38', '>=')) return;
        $entityAttributes = [
            'customer' => [
                'credit_limit' => [
                    'type' => 'static',
                    'label' => 'Credit limit',
                    'input' => 'integer',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'due_limit' => [
                    'type' => 'static',
                    'label' => 'Due limit',
                    'input' => 'integer',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ]

        ];

        foreach ($entityAttributes as $entity => $attributes) {
            foreach ($attributes as $attr => $values) {
                $customerSetup->addAttribute($entity, $attr, $values);
            }
        }
    }

    /**
     * upgrade_2_3_38
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    public function upgrade_2_3_39(ModuleDataSetupInterface $setup, ModuleContextInterface $context, CustomerSetup $customerSetup){
        if (version_compare($context->getVersion(), '2.3.39', '>=')) return;
        $entityAttributes = [
            'customer' => [
                'credit_limit' => [
                    'type' => 'static',
                    'label' => 'Credit limit',
                    'input' => 'text',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'due_limit' => [
                    'type' => 'static',
                    'label' => 'Due limit',
                    'input' => 'text',
                    'required' => false,
                    'visible' => false,
                    'system' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ]

        ];

        foreach ($entityAttributes as $entity => $attributes) {
            foreach ($attributes as $attr => $values) {
                $attribute = $this->eavConfig->getAttribute("customer",$attr);
                if($attribute)
                    $customerSetup->removeAttribute($attribute->getEntityTypeId(),$attr);
                $customerSetup->addAttribute($entity, $attr, $values);
            }
        }
    }

}
