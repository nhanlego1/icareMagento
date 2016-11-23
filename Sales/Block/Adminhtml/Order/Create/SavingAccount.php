<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 27/09/2016
 * Time: 11:49
 */

namespace Icare\Sales\Block\Adminhtml\Order\Create;


use Magento\Backend\Block\Template;
use Magento\Framework\App\ObjectManager;

class SavingAccount extends Template{

    /**
     * @var mixed $savingAccount
     * Customer saving account
     */
    private $savingAccount = false;

    /**
     * Saving account used amount
     * @var int
     */
    private $savingAccountAmount = 0;

    /**
     * Using saving account
     * @var bool
     */
    private $savingAccountUsed = FALSE;
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = []) {
        parent::__construct($context, $data);
        $this->_init();
    }

    /**
     * _init function
     * Get saving account from
     */
    public function _init(){
        $objectManager = ObjectManager::getInstance();
        $session =  $objectManager->get('Magento\Backend\Model\Session\Quote');
        $customerId = $session->getCustomerId();

        $order = $session->getOrder();
        if($order){
            $this->savingAccountUsed = $order->getData('saving_account');
            $this->savingAccountAmount = $order->getData('saving_account_amount');
        }
        /**
         * @var \Icare\Mifos\Helper\Mifos $mifos
         */
        $mifos = $objectManager->create('Icare\Mifos\Helper\Mifos');
        try{
            $installment = $mifos->getInstallment($customerId);
            return $this->savingAccount = $installment->savingsAccount?  $installment->savingsAccount :  FALSE;
        }
        catch (\Exception $e){

        }
    }

    /**
     * getSavingAccount
     * @return int
     */
    public function getSavingAccount(){
        return $this->savingAccount;
    }


    public function getDbSavingAccount(){
        return [
            'used' => $this->savingAccountUsed,
            'amount' => $this->savingAccountAmount
        ];
    }
}