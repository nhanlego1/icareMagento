<?php
namespace Icare\Custom\Helper;


use Icare\Mifos\Helper\Mifos;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class Custom
{

    private static $_instance;
    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';
    const CREDIT_LIMIT = 'credit_limit';
    const DUE_LIMIT = 'due_limit';
    const CUSTOMER = 'customer_cache_';
    const CUSTOMER_REPOSITORY = 'customer_repo_cache_';
    const PRODUCT = 'product_cache_';
    const ORDER = 'order_cache_';
    const LIMIT = 'limit_cache_';

    public function __construct()
    {
        $this->_objectCache = \Magento\Framework\App\ObjectManager::getInstance()->get('Icare\Custom\Model\Cache');
        $this->_object = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_logger = $this->_object->get('\Psr\Log\LoggerInterface');
    }


    /**
     * Create Mifos instance
     * @return mixed
     */
    public static function create()
    {
        if (null === static::$_instance) {
            static::$_instance = new Custom();
        }

        return static::$_instance;
    }

    /**
     * get Credit limit and Due Limit
     * @param string $customerId
     * @return array || null
     */
    public function getCreditDueLimit($customerId)
    {


            $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $bind = [self::CUSTOMER_ENTITY_FIELD => $customerId];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CREDIT_LIMIT, self::DUE_LIMIT]
            );
            $select->where('entity_id = :entity_id');
            $limit = $connection->fetchAll($select, $bind);
            $customer = $this->_object->create('Magento\Customer\Model\Customer')->load($customerId);
            $website = $this->_object->get('Magento\Store\Model\Website')->load($customer->getWebsiteId());
            $store = $website->getStores();
            $store = reset($store);
            if ($limit) {
                $limit = reset($limit);
                $limit['currency_code'] = $store->getCurrentCurrencyCode();
                return $limit;
            } else {
                return null;
            }


    }

    /**
     * Check limit function
     * @param string $orderTotal
     * @param string $customerId
     * @return mixed
     */
    function checkCreditDueLimit($orderTotal, $customerId, $savingAccount = 0, $savingAmount = 0, $productId = null, $store_id = null)
    {
        $this->_logger->track(['$orderTotal' => $orderTotal,
            '$customerId' => $customerId,
            '$savingAccount' => $savingAccount,
            '$savingAmount' => $savingAmount,
            '$productId' => $productId,
            '$store_id' => $store_id
        ],["check credit limit param input"]);
        //check credit before save
        $credit = $this->getCreditDueLimit($customerId);
        $credit_limit = $credit['credit_limit'];
        $due_limit = $credit['due_limit'];
        $total_order = $orderTotal;
        //get number of month
        $numberOfPayment = $this->getNumerOfRepayment(intval($productId), $store_id);
        //get installment
        $installment = Mifos::create()->getInstallment($customerId);
        $result = [];
        if ($installment) {

            $credit_using = isset($installment->installment->totalUsingCreditAmount) ? $installment->installment->totalUsingCreditAmount : 0;
            $due_using = isset($installment->installment->totalUsingDueAmount) ? $installment->installment->totalUsingDueAmount : 0;
            //the credit and due limit can buy after check with mifos
            $limit_due = $due_limit - $due_using;
            $limit_credit = $credit_limit - $credit_using;
            
            
            if ($savingAccount == 1) {
                if ($savingAmount <= 0) {
                    $savingAmount = 0;
                }
                $actual_order_total = $total_order - $savingAmount;
            } else {
                $actual_order_total = $total_order;
            }
            
            
            $this->_logger->track(['limit due' => $limit_due,
                'limit credit' => $limit_credit,
                'number of payment' => $numberOfPayment,
                'actual order total' => $actual_order_total
            ],["check credit limit"]);
            
            if (($limit_due * $numberOfPayment) >= $actual_order_total && $limit_credit >= $actual_order_total) {
                // can buy
                $this->_logger->debug('user can place order');
            } else {
                // and not buy
                //check due limit
                if (($limit_due * $numberOfPayment) < $actual_order_total) {
                    $result[] = new IcareException(__("Due Limit is not enough to place order. Please check with customer to get more due limit."));
                    throw new IcareWebApiException(402, __('Due Limit is not enough to place order. Please check with customer to get more due limit.'), $result);
                }
                //check second credit limit
                if ($limit_credit < $actual_order_total) {
                    $result[] = new IcareException(__("Credit Limit is not enough to place order. Please check with customer to get more credit limit."));
                    throw new IcareWebApiException(402, __("Credit Limit is not enough to place order. Please check with customer to get more credit limit."), $result);
                }
            }


        }

    }

    /**
     * Check limit function
     * @param string $orderTotal
     * @param string $customerId
     * @return mixed
     */
    function checkCreditDueLimitBefore($orderTotal, $customerId)
    {
        //check credit before save
        $credit = $this->getCreditDueLimit($customerId);
        $credit_limit = $credit['credit_limit'];
        $due_limit = $credit['due_limit'];
        $total_order = $orderTotal;
        $installment = Mifos::create()->getInstallment($customerId);
        $result = [];
        $return = true;
        if ($installment) {
            $credit_using = $installment->installment->totalUsingCreditAmount;
            $due_using = $installment->installment->totalUsingDueAmount;
            //check due limit first
            if ($due_limit - ($due_using + $total_order) < 0) {
                $return = false;
            }
            //check second credit limit
            if ($credit_limit - ($credit_using + $total_order) < 0) {
                $return = false;
            }
        }
        return $return;
    }

    /**
     * load customer by cache before
     * @param string $customerId
     * @return mixed;
     */
    public function customCustomerLoad($customerId)
    {
        $cache = $this->_objectCache;

        $cacheKey = self::CUSTOMER . $customerId;
        $customer = $cache->load($cacheKey);

        if (!$customer) {
            $customer = $this->_object->create('Magento\Customer\Model\Customer')->load($customerId);
            $data = json_encode($customer);
            $cache->save(serialize($data), $cacheKey);
            return $customer;
        } else {
            $data = json_decode(unserialize($customer));
            return $data;
        }

    }

    /**
     * load customer by cache before
     * @param string $customerId
     * @return mixed;
     */
    public function customCustomerRepositoryLoad($customerId)
    {
        $cache = $this->_objectCache;

        $cacheKey = self::CUSTOMER_REPOSITORY . $customerId;
        $customer = $cache->load($cacheKey);

        if (!$customer) {
            $customerRepository = $this->_object->get('Magento\Customer\Api\CustomerRepositoryInterface');
            $customer = $customerRepository->getById($customerId);
            $data = json_encode($customer);
            $cache->save(serialize($data), $cacheKey);
            return $customer;
        } else {
            $data = json_decode(unserialize($customer));
            return $data;
        }

    }

    /**
     * load order by cache before
     * @param string $orderId
     * @return mixed;
     */
    public function customOrderLoad($orderId)
    {
        $cache = $this->_objectCache;

        $cacheKey = self::ORDER . $orderId;
        $order = $cache->load($cacheKey);
        if (!$order) {
            $order = $this->_object->create('Magento\Sales\Model\Order')->load($orderId);
            $data = json_encode($order);
            $cache->save(serialize($data), $cacheKey);
            return $order;
        }
        $data = json_decode(unserialize($order));
        return $data;
    }

    /**
     * load product by cache before
     * @param string $productId
     * @return mixed;
     */
    public function customProductLoad($productId)
    {
        $cache = $this->_objectCache;

        $cacheKey = self::PRODUCT . $productId;
        $product = $cache->load($cacheKey);
        if (!$product) {
            $product = $this->_object->create('Magento\Catalog\Model\Product')->load($productId);
            $data = json_encode($product);
            $cache->save(serialize($data), $cacheKey);
            return $product;
        }
        $data = json_decode(unserialize($product));
        return $data;
    }

    /**
     * save clientid to customer
     */
    public function saveClientIdCustomer($clientId, $customerId)
    {
        try {
            $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $clientData = [];
            $clientData['client_id'] = $clientId;

            $connection->update(
                self::CUSTOMER_TABLE,
                $clientData,
                $connection->quoteInto('entity_id = ?', $customerId)
            );
        } catch (\Exception $ex) {
            $this->_logger->error($ex->getMessage());
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __($ex->getMessage()), $result);
        }
    }

    /**
     * save clientid to customer
     */
    public function getClientIdCustomer($customerId)
    {

        $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['client_id']
        );
        $select->where('entity_id = :entity_id');
        $clientId = $connection->fetchOne($select, $bind);
        if ($clientId) {
            return $clientId;
        } else {
            return false;
        }

    }

    /**
     * Get OrgId of customer
     */
    public function getCustomerOrgId($customerId)
    {
        $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['organization_id']
        );
        $select->where('entity_id = :entity_id');
        $organization_id = $connection->fetchOne($select, $bind);
        if ($organization_id) {
            return $organization_id;
        } else {
            return false;
        }
    }

    /**
     * check and get deposit of customer
     */
    public function getDespositByCustomer($customerId)
    {
        $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['customer_id' => $customerId, 'is_deposit' => 1];
        $select = $connection->select()->from(
            'icare_deposit',
            ['amount', 'id']
        );
        $select->where('customer_id = :customer_id');
        $select->where('is_deposit = :is_deposit');
        $select->order('id DESC');
        $deposit = $connection->fetchAll($select, $bind);
        if ($deposit) {
            $deposit = reset($deposit);
            return $deposit;
        } else {
            return false;
        }
    }

    /**
     * disable deposit
     */
    public function disableDeposit($customerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        if ($depositData = $this->getDespositByCustomer($customerId)) {
            $deposit = $om->create('Icare\Deposit\Model\Deposit')->load($depositData['id']);
            $deposit->setIsDeposit(0);
            $deposit->setStatus(0);
            $deposit->save();
        }
    }
    /**
     * get Icare center type
     */
    public function getIcareCenterType($customerId){
        $resource = $this->_object->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['icare_center_type']
        );
        $select->where('entity_id = :entity_id');
        $type = $connection->fetchOne($select, $bind);
        if ($type) {
            return $type;
        } else {
            return 0;
        }
    }

    /**
     * Check region exit
     */
    public function checkExistRegion($country_code, $region_name)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['country_id' => $country_code, 'default_name' => $region_name];
        $select = $connection->select()->from(
            'directory_country_region',
            ['region_id']
        );
        $select->where('country_id = :country_id');
        $select->where('default_name = :default_name');
        $regionId = $connection->fetchOne($select, $bind);
        if ($regionId) {
            return $regionId;
        } else {
            return false;
        }
    }
    /**
     * update insert temp table saving
     */
    public function addTempSaving($customerId, $amount){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource  = $om->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $insertValues = [
            'customer_id' => $customerId,
            'amount' => $amount
        ];
        $connection->insert('icare_saving_account',$insertValues);
    }

    /**
     * delete after finish order
     */
    public function deleteTempSaving($customerId){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource  = $om->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $connection->delete('icare_saving_account', ['customer_id = ?' => $customerId]);
    }

    /**
     * Get temp order saving
     */
    /**
     * Check region exit
     */
    public function getTempSaving($customerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()->from(
            'icare_saving_account',
            ['amount']
        );
        $select->where('customer_id = :customer_id');
        $amount = $connection->fetchOne($select, $bind);
        if ($amount) {
            return $amount;
        } else {
            return false;
        }
    }

    public function getInstallmentProduct($productId, $storeId)
    {
        /**
         * @var \Icare\Installment\Helper\Data $installmentHelper
         */
        $installmentHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Icare\Installment\Helper\Data');
        $installment = $installmentHelper->getInstallmentProduct($productId, $storeId);
        $installment = reset($installment);
        return $installment;
    }

    public function getNumerOfRepayment($product_id, $store_id)
    {
        $installmentInfo = $this->getInstallmentProduct($product_id, $store_id);
        return $installmentInfo['number_of_repayment'];
    }

    public function updateOrderItemWithInstallmentInformation($order)
    {
        $orderItemObj = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Sales\Model\Order\Item');
        foreach ($order->getItems() as $orderItem) {
            $orderItem = $orderItemObj->load($orderItem->getId());
            $numberOfRepayment = $orderItem->getInstallmentNumberOfRepayment();
            if (!isset($numberOfRepayment)) {
                $installmentInfo = $this->getInstallmentProduct($orderItem->getProductId(), $order->getStoreId());
                $orderItem->setInstallmentNumberOfRepayment($installmentInfo['number_of_repayment']);
                $orderItem->setInstallmentInformation(json_encode($installmentInfo));
                $orderItem->save();
            }

        }
    }

    /**
     * Get order by Loan
     */
    public function getOrderByLoan($loanId){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['loan_id' => $loanId];
        $select = $connection->select()->from(
            'sales_order',
            ['entity_id']
        );
        $select->where('loan_id = :loan_id');
        $order_id = $connection->fetchOne($select, $bind);
        if ($order_id) {
            $order = $om->create('Magento\Sales\Model\Order')->load($order_id);
            return $order;
        } else {
            return false;
        }
    }

    /**
     * Get product Id by quote id
     */
    public function getProductIdbyQuoteId($quoteId){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['quote_id' => $quoteId];
        $select = $connection->select()->from(
            'quote_item',
            ['product_id']
        );
        $select->where('quote_id = :quote_id');
        $select->order('item_id DESC');
        $product_id = $connection->fetchOne($select, $bind);
        if ($product_id) {
            return $product_id;
        } else {
            return false;
        }
    }

    /**
     * Get time zone per country
     */
    public function getTimeZoneWebsite($websiteId, $path = 'general/locale/timezone'){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['scope_id' => $websiteId,'path'=>$path];
        $select = $connection->select()->from(
            'core_config_data',
            ['value']
        );
        $select->where('scope_id = :scope_id');
        $select->where('path = :path');
        $timeZone = $connection->fetchOne($select, $bind);
        if ($timeZone) {
            return $timeZone;
        } else {
            return false;
        }
    }

    /**
     * @param int $organizationId
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomer
     * @return array
     */
    public function getCustomerByOrganization(
        $organizationId,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomer
    ) {
        $collection = $collectionCustomer->create()
            ->addFilter('organization_id', $organizationId);
        $customers = $collection->load();

        return $customers->getItems();
    }

}