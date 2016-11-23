<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\Sales\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetup;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetup
     */
    private $salesSetup;

    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @param \Magento\Eav\Setup\EavSetup $salesSetup
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetup $salesSetup,
            Builder $sequenceBuilder,
            SequenceConfig $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
        $this->salesSetup = $salesSetup;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Install order statuses from config
         */
        if (version_compare($context->getVersion(), '2.0.39', '<')) {
            $data = [];
            $statuses = [
                'new' => __('New'),
                'confirmed' => __('Confirmed'),
                'invoiced' => __('Invoiced'),
                'packed' => __('Packed'),
                'shipped' => __('Shipped'),
                'delivered' => __('Delivered'),
                'payment_disbursed' => __('Payment Disbursed'),
                'ready_payment' => __('Ready For Payment'),
                'delivery_failed' => __('Delivery Failed'),
            ];
            foreach ($statuses as $code => $info) {
                if ($this->checkExistStatus($code)) {
                    $data[] = ['status' => $code, 'label' => $info];
                }
            }
            if ($data) {
                $setup->getConnection()
                    ->insertArray($setup->getTable('sales_order_status'), [
                        'status',
                        'label'
                    ], $data);
            }
            $this->salesSetup->removeAttribute(\Magento\Sales\Model\Order::ENTITY, 'saving_account');
        }

        if (version_compare($context->getVersion(), '2.0.41', '<')) {
            $this->upgradePrefixIncrementId($setup);
        }

        $setup->endSetup();
    }

    /**
     * check exist status
     */
    public function checkExistStatus($status){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['status' => $status];
        $select = $connection->select()->from(
                'sales_order_status',
                ['status']
        );
        $select->where('status = :status');
        $statuses = $connection->fetchOne($select, $bind);
        if($statuses){
                return false;
        }else{
                return true;
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradePrefixIncrementId($setup) {
        $salesSequenseTable = $setup->getTable('sales_sequence_profile');
        $sequenseMetaTable = $setup->getTable('sales_sequence_meta');
        $storesTable = $setup->getTable('store');
        $websiteTable = $setup->getTable('store_website');

        $select = $setup->getConnection()->select()->from(
            $sequenseMetaTable,
            ['meta_id']
        )->joinLeft(
            ['st' => $storesTable],
            "(st.store_id = {$sequenseMetaTable}.store_id)",
            []
        )->joinLeft(
            ['wt' => $websiteTable],
            "(wt.website_id = st.website_id)",
            ['code']
        )->where(
            "{$sequenseMetaTable}.store_id NOT IN (0, 1)"
        )->where(
            "wt.code IS NOT NULL"
        );
        $results = $setup->getConnection()->fetchAll($select);

        foreach ($results as $data) {
            $code = strtoupper($data['code']);
            $bind = ['prefix' => $code];
            $where = ['meta_id = ?' => (int)$data['meta_id']];
            $setup->getConnection()->update($salesSequenseTable, $bind, $where);
        }
    }
}
