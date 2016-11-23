<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Model;

use Icare\Deposit\Api\DepositApiInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class DepositApi implements DepositApiInterface
{
    /** @var \Psr\Log\LoggerInterface $_logger */
    private $_logger;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Icare\Deposit\Model\DepositFactory
     */
    protected $_depositFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Icare\Deposit\Model\DepositFactory $depositFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Icare\Deposit\Model\DepositFactory $depositFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_depositFactory = $depositFactory;
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
    }

    /**
     *
     * @param string $customer_id
     * @return array
     */
    public function getListByUser($user_id)
    {
        try {
            $deposit = $this->_depositFactory->create();
            $collection = $deposit->loadByUser($user_id);
            if (!empty($collection)) {
                $data = [];
                $result = [];
                $total = 0;
                foreach ($collection as $item)
                {
                    $customer_id = $item->getCustomerId();
                    $customer = $this->_customerFactory->create();
                    $customer->load($customer_id);

                    $data[] = array(
                        'id' => $item->getId(),
                        'user_id' => $user_id,
                        'amount' => $item->getAmount(),
                        'creation_time' => $item->getCreationTime(),
                        'customer_id' => $customer_id,
                        'customer_email' => $customer->getEmail(),
                    );
                    $total += $item->getAmount();
                }
                $result['total_amount'] = $total - $this->getTotalPayment($user_id);
                $result['items'] = $data;
                return [$result];
          } else {
              throw new IcareException(__("Deposit not found."));
          }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }
    
    
    protected function getTotalPayment($user_id) {
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('icare_deposit_payment', ['IFNULL(sum(transaction_amount), 0)'])->where('user_id = ?', $user_id);
        $amount = $connection->fetchOne($select);
        if (!$amount) {
            return 0;
        }
        return $amount;
    }

}
