<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/12/16
 * Time: 9:48 AM
 */
namespace Icare\Customer\Model;

use Icare\Customer\Api\CustomerInterface;
use Icare\Mifos\Helper\Mifos;
use Icare\MobileSecurity\Helper\ApiHelper;
use Magento\Framework\App\ObjectManager;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Custom\Helper\Custom;

class Customer implements CustomerInterface
{
    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';
    const ICARE_ADDRESS = 'iCare Benefits';

    /** @var \Psr\Log\LoggerInterface $_logger */
    private $_logger;

    /**
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerManager,
        \Magento\Store\Model\Website $websiteManager,
        \Magento\Store\Model\Store $store
    )
    {

        $this->customerRegistry = $customerRegistry;
        $this->addressRepository = $addressRepository;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;

        $this->_customerManager = $customerManager;
        $this->_websiteManager = $websiteManager;
        $this->_store = $store;
        $this->_objectCache = \Magento\Framework\App\ObjectManager::getInstance()->get('Icare\Custom\Model\Cache');
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);

    }


    /**
     * Place order
     * @Icare\Cache\Annotation\Cacheable(cacheName="customers")
     * @api
     * @param string $keyword
     * @param string $countryCode
     * @param string $device_id
     * @return array
     */
    public function getList($keyword, $countryCode,$device_id)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            //get customer by website id and telephone
            $bind = ['telephone' => $keyword, 'website_id' => $countryCode];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD]
            );
            $select->where('telephone = :telephone');
            $select->where('website_id = :website_id');
            $customerId = $connection->fetchOne($select, $bind);
            $data = [];
            if ($customerId) {
                $customer = $this->_customerManager->load($customerId);
                //  $customer = Custom::create()->customCustomerLoad($customerId);
                $website = $this->_websiteManager->load($countryCode);
                $store = $this->_store->load($customer->getStoreId());
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if ($address) {
                    $street = $address->getStreet();
                    $street = reset($street);
                    $data['address_id'] = $address->getId();
                    $data['telephone'] = $keyword;
                    $data['company'] = $address->getCompany();
                    $data['street'] = $street;
                    $data['district'] = $address->getRegion();
                    $data['city'] = $address->getCity();
                    $data['postcode'] = $address->getPostcode();
                } else {
                    $data['telephone'] = '';
                    $data['company'] = '';
                    $data['street'] = '';
                    $data['district'] = '';
                    $data['city'] = '';
                    $data['postcode'] = '';
                }
                $credit = Custom::create()->getCreditDueLimit($customer->getId());
                $data['id'] = $customer->getId();
                $data['full_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $data['website_id'] = $website->getWebsiteId();
                $data['country'] = $website->getName();
                $data['country_code'] = $website->getCode();
                $data['store_id'] = $customer->getStoreId();
                $data['currency_code'] = $store->getCurrentCurrencyCode();
                $data['email'] = $customer->getEmail();
                $data['credit_limit'] = isset($credit['credit_limit']) ? $credit['credit_limit'] : 0;
                $data['due_limit'] = isset($credit['due_limit']) ? $credit['due_limit'] : 0;
                $data['security'] = ApiHelper::getInstance()->getAppData($data['id'],$device_id);

                if (Custom::create()->getClientIdCustomer($customer->getId())) {
                    $data['client_id'] = Custom::create()->getClientIdCustomer($customer->getId());
                } else {
                    $data['client_id'] = 0;
                }
                $data['social_id'] = $this->getSocialId($customer->getId());
                $result['customer'] = $data;

                return $result;

            } else {
                throw new IcareException(__("Customer not found."));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }


    }
    
    /**
     * Get List Customer
     * @Icare\Cache\Annotation\Cacheable(cacheName="customers")
     * @api
     * @param string $keyword
     * @param string $countryCode
     * @param string $device_id
     * @return array
     */
    public function getListCustomer($keyword, $countryCode,$device_id = null)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $customerRegistry = $om->get('Magento\Customer\Model\CustomerRegistry');
            //get customer by website id and telephone
            $bind = ['telephone' => $keyword, 'website_id' => $countryCode];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD]
                );
            $select->where('telephone = :telephone');
            $select->where('website_id = :website_id');
            $customerId = $connection->fetchOne($select, $bind);
            $data = [];
            if ($customerId) {
                $customer = $this->_customerManager->load($customerId);
                //  $customer = Custom::create()->customCustomerLoad($customerId);
                $website = $this->_websiteManager->load($countryCode);
                $store = $this->_store->load($customer->getStoreId());
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if ($address) {
                    $street = $address->getStreet();
                    $street = reset($street);
                    $data['address_id'] = $address->getId();
                    $data['telephone'] = $keyword;
                    $data['company'] = $address->getCompany();
                    $data['street'] = $street;
                    $data['district'] = $address->getRegion();
                    $data['city'] = $address->getCity();
                    $data['postcode'] = $address->getPostcode();
                } else {
                    $data['telephone'] = '';
                    $data['company'] = '';
                    $data['street'] = '';
                    $data['district'] = '';
                    $data['city'] = '';
                    $data['postcode'] = '';
                }
                $credit = Custom::create()->getCreditDueLimit($customer->getId());
                $data['id'] = $customer->getId();
                $data['full_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $data['website_id'] = $website->getWebsiteId();
                $data['country'] = $website->getName();
                $data['country_code'] = $website->getCode();
                $data['store_id'] = $customer->getStoreId();
                $data['currency_code'] = $store->getCurrentCurrencyCode();
                $data['email'] = $customer->getEmail();
                $data['credit_limit'] = isset($credit['credit_limit']) ? $credit['credit_limit'] : 0;
                $data['due_limit'] = isset($credit['due_limit']) ? $credit['due_limit'] : 0;
                $dataSecurity = array();
                $customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
                if ($customerSecure->getPasswordHash() !== null) {
                    $dataSecurity['status'] = 'active';
                } else {
                    $dataSecurity['status'] = 'unregister';
                }
                $data['security'] = $dataSecurity;
                if (Custom::create()->getClientIdCustomer($customer->getId())) {
                    $data['client_id'] = Custom::create()->getClientIdCustomer($customer->getId());
                } else {
                    $data['client_id'] = 0;
                }
                
                $data['social_id'] = $this->getSocialId($customer->getId());
                $is_active = 0;
                if ($customer->getData('is_active') == null) {
                    $is_active = 0;
                } else {
                    $is_active = intval($customer->getData('is_active'));
                }
                $data['is_active'] = $is_active;
                $result['customer'] = $data;
    
                return $result;
    
            } else {
                throw new IcareException(__("Customer not found."));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    
    
    }


    /**
     * @description Search customer by their social ID
     * @Icare\Cache\Annotation\Cacheable(cacheName="customers")
     * @api
     * @param string $social_id
     * @param string $website_id
     * @param string $store_id
     * @return array
     */
    public function getListBySocialId($social_id, $website_id, $store_id = FALSE)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $bind = ['social_id' => $social_id, 'website_id' => $website_id];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD]
            );
            $select->where('social_id = :social_id');
            $select->where('website_id = :website_id');
            if($store_id){
                $bind['store_id'] = $store_id;
                $select->where('store_id = :store_id');
            }
            $customerId = $connection->fetchOne($select, $bind);
            $data = [];
            if ($customerId) {
                $customer = $this->_customerManager->load($customerId);
                $website = $this->_websiteManager->load($website_id);
                $store = $this->_store->load($customer->getStoreId());
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if ($address) {
                    $street = $address->getStreet();
                    $street = reset($street);
                    $data['address_id'] = $address->getId();
                    $data['telephone'] = $this->getTelephone($customerId);
                    $data['company'] = $address->getCompany();
                    $data['street'] = $street;
                    $data['district'] = $address->getRegion();
                    $data['city'] = $address->getCity();
                    $data['postcode'] = $address->getPostcode();
                } else {
                    $data['telephone'] = '';
                    $data['company'] = '';
                    $data['street'] = '';
                    $data['district'] = '';
                    $data['city'] = '';
                    $data['postcode'] = '';
                }
                $credit = Custom::create()->getCreditDueLimit($customer->getId());
                $data['id'] = $customer->getId();
                $data['full_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $data['website_id'] = $website->getWebsiteId();
                $data['country'] = $website->getName();
                $data['country_code'] = $website->getCode();
                $data['store_id'] = $customer->getStoreId();
                $data['currency_code'] = $store->getCurrentCurrencyCode();
                $data['email'] = $customer->getEmail();
                $data['social_id'] = $customer->getSocialId();
                $data['credit_limit'] = isset($credit['credit_limit']) ? $credit['credit_limit'] : 0;
                $data['due_limit'] = isset($credit['due_limit']) ? $credit['due_limit'] : 0;
                if (Custom::create()->getClientIdCustomer($customer->getId())) {
                    $data['client_id'] = Custom::create()->getClientIdCustomer($customer->getId());
                } else {
                    $data['client_id'] = 0;
                }
                $is_active = 0;
                if ($customer->getData('is_active') == null) {
                    $is_active = 0;
                } else {
                    $is_active = intval($customer->getData('is_active'));
                }
                $data['is_active'] = $is_active;
                $result['customer'] = $data;

                return $result;

            } else {
                throw new IcareException(__("Customer not found."));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }


    }

    /**
     * API to get list customer by telephone
     * @Icare\Cache\Annotation\Cacheable(cacheName="customers")
     * @api
     * @param string $telephone
     * @param string $website_id
     * @param string $store_id
     * @return array
     */
    public function getListByPhone($telephone, $website_id, $store_id)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            //get customer by website id and telephone
            $bind = ['telephone' => $telephone, 'website_id' => $website_id, 'store_id' => $store_id,];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD]
            );
            $select->where('telephone = :telephone');
            $select->where('website_id = :website_id');
            $select->where('store_id = :store_id');
            $customerId = $connection->fetchOne($select, $bind);
            $data = [];
            if ($customerId) {
                $customer = $this->_customerManager->load($customerId);
                $website = $this->_websiteManager->load($website_id);
                $store = $this->_store->load($store_id);
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if ($address) {
                    $street = $address->getStreet();
                    $street = reset($street);
                    $data['address_id'] = $address->getId();
                    $data['telephone'] = $telephone;
                    $data['company'] = $address->getCompany();
                    $data['street'] = $street;
                    $data['district'] = $address->getRegion();
                    $data['city'] = $address->getCity();
                    $data['postcode'] = $address->getPostcode();
                } else {
                    $data['telephone'] = '';
                    $data['company'] = '';
                    $data['street'] = '';
                    $data['district'] = '';
                    $data['city'] = '';
                    $data['postcode'] = '';
                }
                $credit = Custom::create()->getCreditDueLimit($customer->getId());
                $data['id'] = $customer->getId();
                $data['full_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $data['website_id'] = $website_id;
                $data['country'] = $website->getName();
                $data['country_code'] = $website->getCode();
                $data['store_id'] = $store_id;
                $data['currency_code'] = $store->getCurrentCurrencyCode();
                $data['email'] = $customer->getEmail();
                $data['social_id'] = $customer->getSocialId();
                $data['credit_limit'] = isset($credit['credit_limit']) ? $credit['credit_limit'] : 0;
                $data['due_limit'] = isset($credit['due_limit']) ? $credit['due_limit'] : 0;
                if (Custom::create()->getClientIdCustomer($customer->getId())) {
                    $data['client_id'] = Custom::create()->getClientIdCustomer($customer->getId());
                } else {
                    $data['client_id'] = 0;
                }
                $is_active = 0;
                if ($customer->getData('is_active') == null) {
                    $is_active = 0;
                } else {
                    $is_active = intval($customer->getData('is_active'));
                }
                $data['is_active'] = $is_active;
                $result['customer'] = $data;

                return $result;

            } else {
                throw new IcareException(__("Customer not found."));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }


    }

    /**
     * @api
     * @param $storeId
     * @param $keyword
     * @return array
     */
    public function searchProducts($storeId, $keyword)
    {
        $result = array();
        if (empty($storeId) || $storeId == 0) {
            $result[] = new IcareException(__("Store Id is required."));
        }
        if (empty($storeId)) {
            $result[] = new IcareException(__("Keyword is required."));
        }
        if ($result) {
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }
        $result = array();
        try {
            $objectManager = ObjectManager::getInstance();
            $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

            $products = $productCollection->create()
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addStoreFilter($storeId)
                ->addAttributeToFilter(array(array('attribute' => 'type_id', 'neq' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)))
                ->addAttributeToFilter(array(array('attribute' => 'visibility', 'neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)));

            if ($keyword !== '_all_') {
                $products->addAttributeToFilter(
                    array(array('attribute' => 'sku', 'like' => '%' . $keyword . '%'), array('attribute' => 'name', 'like' => '%' . $keyword . '%'))
                );
            }

            $products->load();
            if (empty($products)) {
                return $result;
            }

            $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $result = $this->convertProducts($products, $store);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        return $result;
    }


    /**
     * Convert product list to array
     * @param $products
     * @param $store
     * @return array
     */
    private function convertProducts($products, $store)
    {
        $result = array();

        foreach ($products as $product) {
            $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

            $item = array(
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice(),
                'image' => $imageUrl,
                'description' => $product->getDescription()
            );

            $result[] = $item;
        }

        return $result;
    }

    /**
     * save credit when add customer
     * @param string $customerId
     * @param string $creditLimit
     * @param string $dueLimit
     * @return mixed
     */

    public function customerCredit($customerId, $creditLimit, $dueLimit)
    {
        $result = array();
        $creditData = [];
        if (empty($customerId) || !is_numeric($customerId) || $customerId == 0) {
            $result[] = new IcareException(__("Empty customer Id."));
        }
        if (!is_numeric($creditLimit)) {
            $result[] = new IcareException(__("Credit Limit must be numeric."));
        }
        if (!is_numeric($dueLimit)) {
            $result[] = new IcareException(__("Due Limit must be numeric."));
        }
        if ($result) {
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }
            $creditData['credit_limit'] = $creditLimit;
            $creditData['due_limit'] = $dueLimit;
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            if ($creditData) {
                try {
                    $connection->update(
                        self::CUSTOMER_TABLE,
                        $creditData,
                        $connection->quoteInto('entity_id = ?', $customerId)
                    );
                    $data['status']['status'] = true;
                    //set data to cache when update credit limit
                    $cache = $this->_objectCache;
                    $cacheKey = Custom::LIMIT . $customerId;
                    $customer = $om->create('Magento\Customer\Model\Customer')->load($customerId);
                    $website = $om->get('Magento\Store\Model\Website')->load($customer->getWebsiteId());
                    $store = $website->getStores();
                    $store = reset($store);
                    $limit = [];
                    $limit['credit_limit'] = $creditLimit;
                    $limit['due_limit'] = $dueLimit;
                    $limit['currency_code'] = $store->getCurrentCurrencyCode();
                    $data = json_encode($limit);
                    $cache->save(serialize($data), $cacheKey);
                    return $data;
                } catch (\Exception $ex) {
                    $result[] = new IcareException(__($ex->getMessage()));
                    throw new IcareWebApiException(401, __("Web internal Api error."), $result);
                }
            } else {
                throw new IcareException(__('Empty Credit limit and due limit.'));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }


    /**
     * @api
     * @param string $websiteId
     * @return mixed
     */
    public function customerContent($websiteId)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            //get customer by website id and telephone
            $bind = ['website' => $websiteId, 'category' => 1];
            $select = $connection->select()->from(
                'cms_page',
                ['page_id']
            );
            $select->where('website = :website');
            $select->where('category = :category');
            $id = $connection->fetchOne($select, $bind);
            $data = [];
            if ($id) {
                $page = $om->create('Magento\Cms\Model\Page')->load($id);
                $data = $page->getData();
                $result['page'] = $data;
                return $result;
            } else {
                throw new IcareException(__("Content not found"));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }

    }

    /**
     *
     * @param \Icare\Customer\Api\Data\CustomerInfoInterface $customerInfo
     * @return string
     */
    public function customerCreate(\Icare\Customer\Api\Data\CustomerInfoInterface $customerInfo)
    {
        $error = [];
        if (empty($customerInfo->getFullName())) {
            $error[] = new IcareException(__("Full name is required."));
        }
        if (empty($customerInfo->getWebsiteId())) {
            $error[] = new IcareException(__("Website Id (tenants Id) is required."));
        }
//        if (empty($customerInfo->storeId)) {
//            $error[] = new IcareException(__("Store Id is required."));
//        }
        if (empty($customerInfo->getPostalCode())) {
            $error[] = new IcareException(__("Postal code is required."));
        }
        if (empty($customerInfo->getCity())) {
            $error[] = new IcareException(__("City is required."));
        }
        if (empty($customerInfo->getTelephone())) {
            $error[] = new IcareException(__("Telephone is required."));
        }
        if ($error) {
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $error);
        }
        $dataCustomer = [];
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $om->create('Magento\Customer\Model\Customer');
            $website = $om->get('Magento\Store\Model\Website')->load($customerInfo->getWebsiteId());
            if (empty($customerInfo->getStoreId())) {
                $stores = $website->getStores();
                $is_vn = false;
                foreach ($stores as $store) {
                    if ($store->getWebsiteCode() == 'vn') {
                        if ($store->getCode() == 'vn_south') {
                            $customerInfo->setStoreId($store->getId());
                            $is_vn = true;
                            break;
                        }
                    } 
                }
                if ($is_vn == false) {
                    $store = reset($stores);
                    $customerInfo->setStoreId($store->getId());
                }
            }

            if (empty($customerInfo->getEmail())) {
                $customer->setEmail('customer' . time() . '@icarebenefits.com');
            } else {
                if (strpos($customerInfo->getEmail(), '@') === false) {
                    $customer->setEmail($customerInfo->getEmail() . '@icarebenefits.com');
                } else {
                    $customer->setEmail($customerInfo->getEmail());
                }

            }
            $fullName = explode(' ', trim($customerInfo->getFullName()));
            $firstname = $fullName[0];
            if (count($fullName) > 1) {
                $lastname = str_replace($fullName[0], '', $customerInfo->getFullName());
                if (empty($lastname)) {
                    $lastname = $fullName[0];
                }
            } else {
                $lastname = $fullName[0];
            }
            $customer->setFirstname($firstname);
            $customer->setLastname(trim($lastname));
            $customer->setWebsiteId($customerInfo->getWebsiteId());
            $customer->setGender($customerInfo->getGender());
            $customer->setDob($customerInfo->getDob());
            $customer->setStoreId($customerInfo->getStoreId());
            //clear special character from telephone
            $telephone = str_replace('(', '', $customerInfo->getTelephone());
            $telephone = str_replace(')', '', $telephone);
            $telephone = str_replace('-', '', $telephone);
            $telephone = str_replace('+', '', $telephone);
            $customer->setTelephone($telephone);
            $customer->setCreditLimit($customerInfo->getCreditLimit());
            $customer->setDueLimit($customerInfo->getDueLimit());
            $customer->setOrganizationId($customerInfo->getOrganizationId());
            $customer->setOrganizationName($customerInfo->getOrganizationName());
            $customer->setEmployerId($customerInfo->getEmployerId());
            $customer->setSocialId($customerInfo->getSocialId());
            $customer->setSocialType($customerInfo->getSocialType());
            if ($customerInfo->getIsActive() == null) {
                $customerInfo->setIsActive(true);
            }
            $customer->setData('is_active', $customerInfo->getIsActive());

            if (!$this->checkCustomerExisted($customerInfo->getWebsiteId(), $customerInfo->getCustomerId())) {
                $customer->save();
                $updateData = [];
                $updateData['telephone'] = $telephone;
                $updateData['organization_id'] = $customerInfo->getOrganizationId();
                $updateData['organization_name'] = $customerInfo->getOrganizationName();
                $updateData['employer_id'] = $customerInfo->getEmployerId();
                $updateData['social_id'] = $customerInfo->getSocialId();
                $updateData['social_type'] = $customerInfo->getSocialType();
                $this->updateCustomerData($customer->getId(), $updateData);
                if (!empty($customerInfo->getCreditLimit()) && !empty($customerInfo->getDueLimit())) {
                    $this->customerCredit($customer->getId(), $customerInfo->getCreditLimit(), $customerInfo->getDueLimit());
                }
                $dataCustomer['customer'] = $customer->getData();
                $address = $om->create('Magento\Customer\Model\Address');
                $address->setCustomerId($customer->getId());
                $address->setCountryId(strtoupper($website->getCode()));
                if (!empty($customerInfo->getAddress())) {
                    $address->setStreet(array($customerInfo->getAddress()));
                } else {
                    $address->setStreet(array(self::ICARE_ADDRESS));
                }

                if (!empty($customerInfo->getCompany())) {
                    $address->setCompany($customerInfo->getCompany());
                } else {
                    $address->setCompany('');
                }
                $address->setPostcode($customerInfo->getPostalCode());
                $address->setTelephone($telephone);
                $address->setCity($customerInfo->getCity());
                $address->setFirstname($firstname);
                $address->setLastname(trim($lastname));

                // buid code region = websitecode + '_region_1'
                // check region exist, neu do region_id ,
                // neu khong co thi tao region moi
                // $address->setRegionId($region_id);
                $region_name = strtolower($website->getCode()) . '_region_1';
                $region_id = $this->checkExistRegion(strtoupper($website->getCode()),$region_name);
                if ($region_id == FALSE) {
                    $region = $om->create('Magento\Directory\Model\Region');
                    $region->setCountryId(strtoupper($website->getCode()));
                    $region->setCode($region_name);
                    $region->setDefaultName($region_name);
                    $region->save();
                    $region_id = $region->getRegionid();
                }
                $address->setRegionId($region_id);
                $address->setIsDefaultShipping(1);
                $address->setIsDefaultBilling(1);
                $address->save();
                $dataCustomer['customer']['addresses'] = $address->getData();
                unset($dataCustomer['customer']['addresses']['attributes']);
            } else {
               $this->_logger->info(sprintf('Update Customer[customerId=%s,credit_limit=%s,due_limit=%s,is_active=%s]',
                   $customerInfo->getCustomerId(), $customerInfo->getCreditLimit(), $customerInfo->getDueLimit(), $customerInfo->getIsActive()));
               $customer = $this->customerFactory->create()->load($customerInfo->getCustomerId());
               //$customer->setCreditLimit($customerInfo->getCreditLimit());
               //$customer->setDueLimit($customerInfo->getDueLimit());
               $customer->setData('is_active', $customerInfo->getIsActive());
               $customer->setData('credit_limit', $customerInfo->getCreditLimit());
               $customer->setData('due_limit', $customerInfo->getDueLimit());
               $customer->save();
               $dataCustomer = $customer->getData();
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        return $dataCustomer;
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
     * @param $customerId
     * @param $updateData
     * @throws IcareWebApiException
     */
    protected function updateCustomerData($customerId, $updateData)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        try {
            $connection->update(
                self::CUSTOMER_TABLE,
                $updateData,
                $connection->quoteInto('entity_id = ?', $customerId)
            );
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Error webapi."), $result);
        }
    }

    /**
     * Update telephone
     */
    public function updateTelephone($customerId, $telephone)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $telephoneData = [];
            $telephoneData['telephone'] = $telephone;

            $connection->update(
                self::CUSTOMER_TABLE,
                $telephoneData,
                $connection->quoteInto('entity_id = ?', $customerId)
            );
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("The phone number is ready exist. Please choose other phone number."), $result);
        }
    }
    
    public function checkCustomerExisted($websiteId, $customerId) {
        if ($customerId == null || $customerId <= 0) {
            return false;
        }
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId, 'website_id' => $websiteId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            [self::CUSTOMER_ENTITY_FIELD]
            );
        $select->where('entity_id = :entity_id');
        $select->where('website_id = :website_id');
        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check telephone and website Id
     */
    public function checkTelephoneWebsiteId($telephone, $websiteId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['telephone' => $telephone, 'website_id' => $websiteId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            [self::CUSTOMER_ENTITY_FIELD]
        );
        $select->where('telephone = :telephone');
        $select->where('website_id = :website_id');
        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Update telephone
     */
    public function updateOrgType($customerId, $org_id = '', $org_name = '', $employer_id = '', $social_id = '', $social_type = '')
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $updateData = [];
        $updateData['organization_id'] = $org_id;
        $updateData['organization_name'] = $org_name;
        $updateData['employer_id'] = $employer_id;
        $updateData['social_id'] = $social_id;
        $updateData['social_type'] = $social_type;
        try {
            $connection->update(
                self::CUSTOMER_TABLE,
                $updateData,
                $connection->quoteInto('entity_id = ?', $customerId)
            );
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Error webapi."), $result);
        }
    }

    /**
     * Get relation product
     */
    public function getRelationProducts($product)
    {
        $relations = $product->getRelatedProducts();
        $related = [];
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $productObjet = $om->create('Magento\Catalog\Model\Product');

        foreach ($relations as $relation) {
            $product = $productObjet->load($relation->getId());
            $item = array(
                'id' => $product->getId(),
                'title' => $product->getName(),
                'month' => $relation->getSku(),
                'description' => $product->getDescription()
            );
            $related[] = $item;
        }
        return $related;
    }


    private function getCustomOptionProducts($products)
    {
        $options = $products->getOptions();
        $renderOptions = array();
        foreach ($options as $option) {
            $renderOptionsValue = array();
            foreach ($option->getValues() as $optionType) {
                $renderOptionsValue[] = [
                    'option_type_id' => $optionType->getOptionTypeId(),
                    'value' => $optionType->getStoreTitle() ? $optionType->getStoreTitle() : $optionType->getTitle()
                ];
            }
            $renderOptions[] = [
                'option_id' => $option->getOptionId(),
                'title' => $option->getStoreTitle() ? $option->getStoreTitle() : $option->getTitle(),
                'value' => $renderOptionsValue
            ];
        }
        return $renderOptions;

    }

    private function getInstallmentProducts($product, $storeId)
    {

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();

        $select = $connection->select()->from('icare_installment_product_relation as main_table',
            ['installment.installment_id', 'installment.title',
                'installment.number_of_repayment', 'installment.description'])
            ->joinInner(['installment' => 'icare_installment_entity'],
                'main_table.installment_id = installment.installment_id',
                [])
            ->where('main_table.product_id = ?', $product->getId())
            ->where('main_table.store_id = ?', $storeId)
            ->where('installment.is_active = ?', true);
        $rows = $connection->fetchAssoc($select);
        $installments = [];
        foreach ($rows as $row) {
            $installments[] = array(
                'id' => $row['installment_id'],
                'title' => $row['title'],
                'month' => $row['number_of_repayment'],
                'description' => $row['description']
            );
        }
        return $installments;
    }


    /**
     * Convert product list to array
     * @param $products
     * @param $store
     * @return array
     */
    private function convertProductsV3($products, $store)
    {
        $result = array();
        $iCareHelper = ObjectManager::getInstance()->get('Icare\Custom\Helper\ICareHelper');
        foreach ($products as $product) {
            $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
            $price_after_tax = $iCareHelper->getPriceIncludeTax($product, $store);
            $tax_infos = $iCareHelper->getTaxRatePercent($product, $store);
            $item = array(
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice(),
                'tax' => $price_after_tax - $product->getPrice(),
                'price_after_tax' => $price_after_tax,
                'is_include_tax' => true,
                'image' => $imageUrl,
                'description' => $product->getDescription(),
                'tax_info' => $tax_infos
            );
            $relate = $this->getInstallmentProducts($product, $store->getId());
            if ($relate) {
                $item['installment'] = $relate;
            }
            $option = $this->getCustomOptionProducts($product);
            if ($option) {
                $item['option'] = $option;
            }
            $medias = $iCareHelper->getProductMedias($product, $store);
            $item['medias'] = $medias;
            $result[] = $item;
        }

        return $result;
    }

    /**
     * API to get list product V3
     * @api
     * @param string $storeId
     * @param string $keyword
     * @return array
     */
    public function listProductSearchV3($storeId, $keyword, $pageNum, $pageSize)
    {
        $result = array();
        if (empty($storeId) || $storeId == 0) {
            $result[] = new IcareException(__("Store Id is required."));
        }
        if (empty($keyword)) {
            $result[] = new IcareException(__("Keyword is required."));
        }
        if ($result) {
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }
        $result = array();
        try {
            $objectManager = ObjectManager::getInstance();
            $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

            $products = $productCollection->create()
                ->setStoreId($storeId)
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('tax_class_id')
                ->addStoreFilter($storeId)
                ->addAttributeToFilter(array(array('attribute' => 'type_id', 'neq' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)))
                ->addAttributeToFilter(array(array('attribute' => 'status', 'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))
                ->addAttributeToFilter(array(array('attribute' => 'visibility', 'neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)));

            if ($keyword !== '_all_') {
                $products->addAttributeToFilter(
                    array(array('attribute' => 'sku', 'like' => '%' . $keyword . '%'), array('attribute' => 'name', 'like' => '%' . $keyword . '%'))
                );
            }
            if ($pageNum > 0 && $pageSize > 0) {
                $products->setPage($pageNum, $pageSize);
            }


            $products->load();
            if (empty($products)) {
                return $result;
            }

            $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);

            $productData = $this->convertProductsV3($products, $store);
            $isLastPage = false;
            if (count($productData) < $pageSize) {
                $isLastPage = true;
            }
            $result[] = array('products' => $productData, 'isLastPage' => $isLastPage);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        return $result;
    }

    /**
     * add Deposit To Customer
     * @param $customer_id
     * @param $user_id
     * @param $amount
     * @return mixed
     */
    public function addDepositToCustomer($customer_id, $user_id, $amount)
    {
        $om = ObjectManager::getInstance();
        $result = array();
        if (empty($customer_id) || $customer_id == 0) {
            $result[] = new IcareException(__("Customer Id is required."));
        }
        if (empty($user_id) || $user_id == 0) {
            $result[] = new IcareException(__("User id is required."));
        }
        if (empty($amount) || $amount == 0) {
            $result[] = new IcareException(__("Amount is required."));
        }
        if ($result) {
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }

        //add deposit to database for record
        $deposit = $om->create('Icare\Deposit\Model\Deposit');
        $deposit->setCustomerId($customer_id);
        $deposit->setUserId($user_id);
        $deposit->setAmount($amount);
        $deposit->setIsDeposit(1);
        $deposit->setStatus(1);
        try {
            $deposit->save();
            //add deposit to mifos
            Mifos::create()->addDeposit($customer_id, $amount);
            $status = array('code' => 200, 'status' => true,'amount'=> $amount);
            return [$status];
        } catch (\Exception $ex) {
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }

    }

    /**
     * get telephone of customer
     */
    public function getTelephone($customerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['telephone']
        );
        $select->where('entity_id = :entity_id');
        $telephone = $connection->fetchOne($select, $bind);
        if ($telephone) {
            return $telephone;
        } else {
            return false;
        }
    }

    /**
     * get social Id of customer
     */
    public function getSocialId($customerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $bind = ['entity_id' => $customerId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['social_id']
        );
        $select->where('entity_id = :entity_id');
        $socialId = $connection->fetchOne($select, $bind);
        if ($socialId) {
            return $socialId;
        } else {
            return false;
        }
    }

    private function loadICareCenterInfo($icareCenter)
    {
        $icare_center_type = $icareCenter['icare_center_type'];
        $customer = $this->customerFactory->create()->load($icareCenter['entity_id']);
        $website = $this->_websiteManager->load($customer->getWebsiteId());
        $addresses = $customer->getAddresses();
        $datas = array();
        if ($addresses) {
            foreach ($addresses as $address) {
                if ($address->getData('is_active')) {
                    $street = $address->getStreet();
                    $datas[] = [
                        "icare_id" => $icareCenter['entity_id'],
                        "icare_center_type" => $icare_center_type,
                        "website_id" => $customer->getWebsiteId(),
                        "address_id" => $address->getData('entity_id'),
                        "street" => reset($street),
                        "telephone" => $address->getData('telephone'),
                        "company" => $address->getData('company'),
                        "district" => $address->getData('region'),
                        "city" => $address->getData('city'),
                        "postcode" => $address->getData('postcode'),
                        'full_name' => $address->getName(),
                        'country' => $website->getName(),
                        'country_code' => $website->getCode(),
                    ];
                }

            }
        }
        return $datas;
    }

    /**
     * API to get list customer by telephone
     * @api
     * @param string $website_id
     * @return array
     */
    public function getICareCenter($website_id)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            //get customer by website id and telephone
            $bind = ['website_id' => $website_id];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD, 'icare_center_type']
            );
            $select->where('website_id = :website_id');
            $select->where('icare_center_type is not null and icare_center_type > 0 and is_active = 1');
            $icareCenters = $connection->fetchAssoc($select, $bind);
            $data = [];
            $result = [];
            if ($icareCenters) {
                foreach ($icareCenters as $icareCenter) {
                    $data = array_merge($data, $this->loadICareCenterInfo($icareCenter));
                }
            }
            return $data;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }


    }
    

    /**
     * Login by Social ID
     *
     * @param string $social_id
     * @param  string $countryCode
     * @param  string $device_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function loginBySocialId($social_id, $countryCode, $device_id = NULL) {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $customerRegistry = $om->get('Magento\Customer\Model\CustomerRegistry');
            //get customer by website id and telephone
            $bind = ['social_id' => $social_id, 'website_id' => $countryCode,];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD]
            );
            $select->where('social_id = :social_id');
            $select->where('website_id = :website_id');
            $customerId = $connection->fetchOne($select, $bind);
            $data = [];
            if ($customerId) {
                $customer = $this->_customerManager->load($customerId);
                //  $customer = Custom::create()->customCustomerLoad($customerId);
                $website = $this->_websiteManager->load($countryCode);
                $store = $this->_store->load($customer->getStoreId());
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if ($address) {
                    $street = $address->getStreet();
                    $street = reset($street);
                    $data['address_id'] = $address->getId();
                    $data['telephone'] = $address->getTelephone();
                    $data['company'] = $address->getCompany();
                    $data['street'] = $street;
                    $data['district'] = $address->getRegion();
                    $data['city'] = $address->getCity();
                    $data['postcode'] = $address->getPostcode();
                } else {
                    $data['telephone'] = '';
                    $data['company'] = '';
                    $data['street'] = '';
                    $data['district'] = '';
                    $data['city'] = '';
                    $data['postcode'] = '';
                }
                $credit = Custom::create()->getCreditDueLimit($customer->getId());
                $data['id'] = $customer->getId();
                $data['full_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $data['website_id'] = $website->getWebsiteId();
                $data['country'] = $website->getName();
                $data['country_code'] = $website->getCode();
                $data['store_id'] = $customer->getStoreId();
                $data['currency_code'] = $store->getCurrentCurrencyCode();
                $data['email'] = $customer->getEmail();
                $data['credit_limit'] = isset($credit['credit_limit']) ? $credit['credit_limit'] : 0;
                $data['due_limit'] = isset($credit['due_limit']) ? $credit['due_limit'] : 0;
                $dataSecurity = array();
                $customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
                if ($customerSecure->getPasswordHash() !== null) {
                    $dataSecurity['status'] = 'active';
                } else {
                    $dataSecurity['status'] = 'unregister';
                }
                $data['security'] = $dataSecurity;
                if (Custom::create()->getClientIdCustomer($customer->getId())) {
                    $data['client_id'] = Custom::create()->getClientIdCustomer($customer->getId());
                } else {
                    $data['client_id'] = 0;
                }
                $data['social_id'] = $this->getSocialId($customer->getId());
                $is_active = 0;
                if ($customer->getData('is_active') == null) {
                    $is_active = 0;
                } else {
                    $is_active = intval($customer->getData('is_active'));
                }
                $data['is_active'] = $is_active;
                $result['customer'] = $data;

                return $result;

            } else {
                throw new IcareException(__("Customer not found."));
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }
}