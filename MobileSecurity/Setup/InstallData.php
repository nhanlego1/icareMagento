<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Setup;
use Icare\MobileSecurity\Model\MobileSecurity;
use Magento\Framework\App\ObjectManager;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        //Update for passcode
        $om = ObjectManager::getInstance();
        $setup->getConnection()->insert($setup->getTable('variable'), [
            'code' => MobileSecurity::VAR_HASH_SALT_KEY,
            'name' => 'iCare Member Passcode'
        ]);
        /**@var \Magento\Variable\Model\Variable $var **/
        $var = $om->create('Magento\Variable\Model\Variable');
        $var->loadByCode('icare_member_passcode');
        $setup->getConnection()->insert($setup->getTable('variable_value'), [
            'variable_id' => $var->getData('variable_id'),
            'store_id' =>0,
            'plain_value' => MobileSecurity::DEFAULT_MEMBER_HASH_SALT
        ]);

        $setup->endSetup();
    }
}
