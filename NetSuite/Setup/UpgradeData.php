<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icare\NetSuite\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->upgrade_2_0_5($setup, $context);
        $setup->endSetup();
    }
    
    /**
     * Change: variable code from <code>aws_sqs_netsuite</code> to <code>aws_sqs_netsuite_send</code>.
     * <p>
     * Add: new variable <code>aws_sqs_netsuite_recv</code>
     * @param ModuleDataSetupInterface $s$etup
     * @param ModuleContextInterface $context
     * @since 2.0.5
     */
    protected function upgrade_2_0_5(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.5', '>=')) return;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Variable\Model\Variable $varSend
         */
        $varSend = $om->create('Magento\Variable\Model\Variable');
        // change variable code
        if (empty($varSend->loadByCode('aws_sqs_netsuite')->getId())) {
            $varSend->setData('plain_value', 'https://sqs.amazonaws.com/account/netsuite');
        }
        else {
            $varSend->setData('plain_value', \str_replace('magento-to-netsuite', 'netsuite', $varSend->getData('plain_value')));
        }
        $varSend->setName('SQS Queue URL for NetSuite');
        $varSend->save();
        /**
         * @var \Magento\Variable\Model\Variable $varRecv
         */
        $varRecv = $om->create('Magento\Variable\Model\Variable');
        $varRecv->setCode('aws_sqs_magento');
        $varRecv->setName('SQS Qeueu URL for Magento');
        $varRecv->setData('plain_value', \str_replace('netsuite', 'magento', $varSend->getData('plain_value')));
        $varRecv->save();
    }
}
