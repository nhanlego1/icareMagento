<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/29/16
 * Time: 2:08 PM
 */

namespace Icare\Custom\Helper;
use Icare\User\Model\RoleConstant;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Controller\Adminhtml\User\Role;

class ICareHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_catalogHelper;
    protected $_addressFactory;
    protected $_storeManager;
    protected $_productModel;
    protected $_customerTaxClassId = 3; // default for retail customer class
    protected $_objectManager;
    protected $_orderModel;
    protected $_taxCalculation;
    protected $_logger;
    protected $_roleCollectionFactory;
    /**@property \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever **/
    protected $aclRetriever;
    const CUSTOMER_TAX_CLASS = 'GENERAL_CUSTOMER_CLASS';

    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Catalog\Helper\Data $catalogHelper,
                                \Magento\Customer\Model\AddressFactory $addressFactory,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Catalog\Model\Product $productModel,
                                \Magento\Sales\Model\Order $orderModel,
                                \Magento\Backend\Model\Auth\Session $authSession,
                                \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
                                \Magento\Tax\Model\Calculation $taxCalculation,
                                \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
        )
    {
        parent::__construct($context);
        $this->_catalogHelper = $catalogHelper;
        $this->_addressFactory = $addressFactory;
        $this->_storeManager = $storeManager;
        $this->_productModel = $productModel;
        $this->_orderModel = $orderModel;
        $this->_taxCalculation = $taxCalculation;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_logger = $context->getLogger();
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->aclRetriever = $aclRetriever;
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('tax_class')->where('class_name = ?', self::CUSTOMER_TAX_CLASS);
        $rows = $connection->fetchAssoc($select);
        $rows = reset($rows);
        if ($rows) {
            $this->_customerTaxClassId = $rows['class_id'];
        }

    }

    public function getCustomerTaxClassId() {
        return $this->_customerTaxClassId;
    }

    public function setCustomerTaxClassId() {
        return $this;
    }

    public function getPriceIncludeTax($product, $store) {
        if (!$product) {
            return 0;
        }
        if (is_int($product)) {
            $product = $this->_productModel->load($product);
        }
        if (is_int($store)) {
            $store = $this->_storeManager->getStore($store);
        }
        // create address
        $address = $this->_addressFactory->create();
        $address
            ->setCountryId(strtoupper($store->getWebsite()->getCode()))
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1');
        return $this->_catalogHelper->getTaxPrice($product, $product->getPrice(), true, $address, $address, $this->getCustomerTaxClassId()
            , $store->getId(), false, true);


    }

    public function getTaxRatePercent($product, $store) {
        if (is_int($product)) {
            $product = $this->_productModel->load($product);
        }

        if (is_int($store)) {
            $store = $this->_storeManager->getStore($store);
        }

        $address = $this->_addressFactory->create();
        $address
            ->setCountryId(strtoupper($store->getWebsite()->getCode()))
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1');

        $request = $this->_taxCalculation->getRateRequest($address,$address, $this->getCustomerTaxClassId(), $store, null);
        $tax_class_id = $product->getData('tax_class_id');
        $info = $this->_taxCalculation->getAppliedRates($request->setProductClassId($tax_class_id));
        $rates = [];
        if ($info && count($info) > 0) {
            foreach ($info[0]['rates'] as $key => $rate) {
                $rates[$key]['tax_code'] = $rate['code'];
                $rates[$key]['tax_percent'] = $rate['percent'];
            }
        }
        return $rates;
    }

    public function getTaxRateCode($order) {
        if (!is_object($order)) {
            $order = $this->_orderModel->load($order);
        }
        $request = $this->_taxCalculation->getRateRequest($order->getShippingAddress(),$order->getBillingAddress(),
            null, $order->getStore(), $order->getCustomerId());
        $taxRateInfo = [];
        foreach ($order->getItems() as $orderItem) {
            $productId = $orderItem->getProductId();
            $product = $this->_productModel->load($productId);
            $tax_class_id = $product->getData('tax_class_id');
            $info = $this->_taxCalculation->getAppliedRates($request->setProductClassId($tax_class_id));
            $rates = [];
            if ($info && count($info) > 0) {
                foreach ($info[0]['rates'] as $rate) {
                    $rates[] = $rate['code'];
                }
                $taxRateInfo[] = ['order_item_id' => $orderItem->getId(), 'tax_rate_code' => implode("|", $rates)];
            }
        }
        return $taxRateInfo;
    }

    public function updateTaxRateInfo($taxRateInfo) {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        foreach ($taxRateInfo as $taxRate) {
            $connection->update('sales_order_item', ['tax_rate_code' => $taxRate['tax_rate_code']], ['item_id = ?' => $taxRate['order_item_id']]);
        }
    }
    
    
    public function updateOrderTimeLine($order_id, $status) {
        $isExistedTimeline = $this->checkOrderTimelineStatus($order_id, $status);
        if ($isExistedTimeline) {
            $this->updateTimelineOrderStatus($isExistedTimeline);
        } else {
            $this->insertOrderTimeline($order_id, $status);
        }
    }
    
    /**
     * check exit order timeline by status
     */
    public function checkOrderTimelineStatus($order_id, $status)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['order_id' => $order_id, 'status' => $status];
        $select = $connection->select()->from(
            'sales_order_timeline',
            ['id']
            );
        $select->where('order_id = :order_id');
        $select->where('status = :status');
        $id = $connection->fetchOne($select, $bind);
        if ($order_id) {
            return $id;
        } else {
            return false;
        }
    }
    
    /**
     * insert order timeline
     */
    public function insertOrderTimeline($order_id, $status)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        date_default_timezone_set('UTC');
        $bind = ['order_id' => $order_id, 'status' => $status, 'updated_date' => date('Y-m-d H:i:s'), 'created_date' => date('Y-m-d H:i:s')];
        try {
            $connection->insert('sales_order_timeline', $bind);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __($ex->getMessage()), $result);
        }
    
    }
    
    /**
     * update status date
     */
    public function updateTimelineOrderStatus($id)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $date = [];
        date_default_timezone_set('UTC');
        $date['updated_date'] = date('Y-m-d H:i:s');
        try {
            $connection->update(
                'sales_order_timeline',
                $date,
                $connection->quoteInto('id = ?', $id)
                );
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __($ex->getMessage()), $result);
        }
    }

    public function checkSpecialUser($user) {
        $roles = $user->getRoles();
        $isSpecialUser = false;
        if (!$roles) {
            return FALSE;
        } else {
            foreach ($roles as $roleId){
                $resources = $this->aclRetriever->getAllowedResourcesByRole($roleId);
                if(in_array(RoleConstant::RES_VIEW_ALLSTORE,$resources)){
                    return TRUE;
                }
            }

            /**@var  \Magento\Authorization\Model\Role[] $roles**/
            $roles = $this->_roleCollectionFactory->create()
                ->addFieldToSelect('role_name')
                ->addFieldToFilter('role_id', ['in' => $roles])
                ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
                ->load();
            foreach($roles as $role) {
                if ($role->getRoleName() == RoleConstant::GLOBAL_SUPPORT || $role->getRoleName() == RoleConstant::ADMINISTRATORS) {
                    return TRUE;
                }
            }

        }
        return FALSE;
    }


    
    public function logRequest(\Magento\Framework\Webapi\Rest\Request $request, $outputData, $is_error = false) {
        try {
            $date = $this->_objectManager->get('\Magento\Framework\Stdlib\DateTime\DateTime');
            $header = json_encode($request->getHeaders()->toArray());
            $request_data = json_encode($request->getRequestData());
            $url = $request->getUriString();
            $method = $request->getHttpMethod();
            if ($request->getHeaders()->get('transaction-id')) {
                $transaction_id = $request->getHeaders()->get('transaction-id')->getFieldValue();
            } else {
                $transaction_id = null;
            }
            
            $created_time = $date->gmtDate();
            $result = $outputData;
            /**
             *
             * @var \Magento\Framework\App\ResourceConnection $resource
             */
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection('log');
            $bind = ['header' => $header, 'request_data' => $request_data,
                'url' => $url, 'transaction_id' => $transaction_id,
                'method' => $method, 'creation_time' => $created_time,
                'result' => $result,
                'is_error' => $is_error
            ];
            $connection->insert('request_log', $bind);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
        }
        
        
        
        
    }
    
    public function getProductMedias($product, $store) {
        if (is_int($product)) {
            $product = $this->_productModel->load($product);
        }
        
        if (is_int($store)) {
            $store = $this->_storeManager->getStore($store);
        }
        
        /**
         *
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $select = $connection->select()->from('catalog_product_entity_media_gallery as main', ['value_id','media_type', 'value as image_value' ])
        ->joinLeft('catalog_product_entity_media_gallery_value as media', 'main.value_id = media.value_id')
        ->joinLeft('catalog_product_entity_media_gallery_value_video as video', 'media.value_id = video.value_id', ['url as video_value'])
        ->where('media.entity_id = ?', $product->getId())
        ->where('media.store_id = ?', $store->getId());
        $rows = $connection->fetchAssoc($select);
        $result = array();
        foreach ($rows as $row) {
          $item['value_id'] = $row['value_id'];
          $item['media_type'] = $row['media_type'];
          $item['thumb'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $row['image_value'];
          if ($item['media_type'] == 'image') {
              $item['url'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $row['image_value'];
          } else if ($item['media_type'] == 'external-video') {
              $item['url'] = $row['video_value'];
          }
          $result[] = $item;
        }
        return $result;
    }
    
    public function authenticate($customer_id, $pincode) {
        $customerRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerRegistry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\CustomerRegistry');
        $encryptor = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Encryption\EncryptorInterface');
        $customerFactory = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\CustomerFactory');
        $eventManager = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Event\ManagerInterface');
        
        $customer = $customerRepository->getById($customer_id);
        $hash = $customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
        if (!$encryptor->validateHash($pincode, $hash)) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        $customer->setConfirmation(null);
        $customerModel = $customerFactory->create()->updateData($customer);
        
        $eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $pincode]
            );
        
        $eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
        return $customer;
    }
    
    
    public function verifyCustomerPincode($customer_id, $pincode) {
        try {
            $customerDataObject = $this->authenticate($customer_id, $pincode);
        } catch (\Exception $e) {
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
                );
        }
        try {
            // currently, we alway allow one session for one customer
            \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Integration\Api\CustomerTokenServiceInterface')->revokeCustomerAccessToken($customer_id);
        } catch (\Exception $e) {
            $this->_logger->debug("customer has not token");
        }
        
        \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class)->resetAuthenticationFailuresCount($customerDataObject->getEmail(), RequestThrottler::USER_TYPE_CUSTOMER);
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Integration\Model\Oauth\TokenFactory')->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }
    
    
    public function verifyCustomerPincodeForConfirm($customer_id, $pincode) {
        try {
            $customerDataObject = $this->authenticate($customer_id, $pincode);
        } catch (\Exception $e) {
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
                );
        }
    }
    
    public function registerPincode($customer_id, $pincode) {
        $customerRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerRegistry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\CustomerRegistry');
        $encryptor = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Encryption\EncryptorInterface');
        $customer = $customerRepository->getById($customer_id);
        $customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($encryptor->getHash($pincode, true));
        $customerRepository->save($customer);
    }
    
    public function resetPincode($customer_id) {
        $customerRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerRegistry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\CustomerRegistry');
        $customer = $customerRepository->getById($customer_id);
        $customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash(null);
        $customerRepository->save($customer);
        return true;
    }
    
    public function validateCustomerToken($customer_id = false, $token) {
        $tokenCollection = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory')
        ->create();
        $tokenCollection->addFieldToFilter('token', $token)->addFilterByRevoked(0);
        if ($customer_id) {
            $tokenCollection->addFilterByCustomerId($customer_id);
        }
        if ($tokenCollection->getSize() == 0) {
            return false;
        } else {
            return true;
        }
    }
    
    public function lockDevice($customer_id) {
        try {
            // currently, we alway allow one session for one customer
            \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Integration\Api\CustomerTokenServiceInterface')->revokeCustomerAccessToken($customer_id);
        } catch (\Exception $e) {
            $this->_logger->debug("customer has not token");
        }
        return true;
    }
}