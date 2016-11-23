<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

namespace Icare\Custom\Setup;

use Icare\Custom\Helper\ICareHelper;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     */
    public function __construct(
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    )
    {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
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
        $data = [];
        $variables = [
            'aws_s3_region' => 'aws_s3_region',
            'aws_s3_bucket' => 'aws_s3_bucket',
            'aws_s3_is_used' => 'aws_s3_is_used'

        ];
        foreach ($variables as $code => $info) {
            if ($this->checkExistStatus($code)) {
                $data[] = ['code' => $code, 'name' => $info];
            }
        }
        if ($data) {
            $setup->getConnection()->insertArray($setup->getTable('variable'), ['code', 'name'], $data);
        }

        //add value for each code
        $value = [];
        foreach ($variables as $code => $info) {
            if ($this->getVariableCode($code)) {
                if ('aws_s3_region' == $code && $this->checkExistValue($this->getVariableCode($code))) {
                    $value[] = ['variable_id' => $this->getVariableCode($code), 'plain_value' => "ap-southeast-1"];
                }
                if ('aws_s3_bucket' == $code && $this->checkExistValue($this->getVariableCode($code))) {
                    $value[] = ['variable_id' => $this->getVariableCode($code), 'plain_value' => "icbmagento"];
                }
                if ('aws_s3_is_used' == $code && $this->checkExistValue($this->getVariableCode($code))) {
                    $value[] = ['variable_id' => $this->getVariableCode($code), 'plain_value' => "1"];
                }

            }
        }
        if ($value) {
            $setup->getConnection()->insertArray($setup->getTable('variable_value'), ['variable_id', 'plain_value'], $value);
        }

        if (version_compare($context->getVersion(), '2.0.14') < 0) {
            $data = ['class_name' => ICareHelper::CUSTOMER_TAX_CLASS,
            'class_type' => 'CUSTOMER'];
            $setup->getConnection()->insert($setup->getTable('tax_class'),$data);
        }

        $setup->endSetup();
    }

    /**
     * check exist status
     */
    public function checkExistStatus($variable)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['code' => $variable];
        $select = $connection->select()->from(
            'variable',
            ['code']
        );
        $select->where('code = :code');
        $statuses = $connection->fetchOne($select, $bind);
        if ($statuses) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * check exist status
     */
    public function getVariableCode($variable)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['code' => $variable];
        $select = $connection->select()->from(
            'variable',
            ['variable_id']
        );
        $select->where('code = :code');
        $code = $connection->fetchOne($select, $bind);
        if ($code) {
            return $code;
        } else {
            return false;
        }
    }

    /**
     * check exist status
     */
    public function checkExistValue($variable)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['variable_id' => $variable];
        $select = $connection->select()->from(
            'variable_value',
            ['variable_id']
        );
        $select->where('variable_id = :variable_id');
        $code = $connection->fetchOne($select, $bind);
        if ($code) {
            return false;
        } else {
            return true;
        }
    }
}
