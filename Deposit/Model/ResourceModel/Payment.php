<?php
namespace Icare\Deposit\Model\ResourceModel;
class Payment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb 
{
    protected function _construct()
    {
        $this->_init('icare_deposit_payment','payment_id');
    }
}
