<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/12/16
 * Time: 9:48 AM
 */
namespace Icare\IcareOrderApi\Model;

use Icare\Custom\Helper\Custom;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\IcareOrderApi\Api\Data\AddressValueInterface;
use Icare\IcareOrderApi\Api\Data\GetOrderListInfoByCustomerInterface;
use Icare\IcareOrderApi\Api\Data\GetOrderListInfoInterface;
use Icare\IcareOrderApi\Api\Data\OptionValueInterface;
use Icare\IcareOrderApi\Api\IcareOrderInterface;
use Icare\MobileSecurity\Helper\ApiHelper;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\IntegrationException;

class IcareOrder implements IcareOrderInterface
{

    private $orderRepository;

    private $searchCriteriaBuilder;

    private $filterBuilder;

    const CUSTOMER_TABLE = 'customer_entity';

    const CUSTOMER_ENTITY_FIELD = 'entity_id';

    const CREDIT_LIMIT = 'credit_limit';

    const DUE_LIMIT = 'due_limit';

    private $excludeOrderStatus = [
        // \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED
    ];

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $_productFactory;

    /**
     *
     * @var \Icare\Installment\Helper\Data
     */
    private $_installmentHelper;

    /**
     *
     * @var \Icare\Cms\Helper\Rating
     */
    private $_ratingHelper;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Data\Form\FormKey $formkey
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Icare\Installment\Helper\Data $installmentHelper
     * @param \Icare\Cms\Helper\Rating $ratingHelper
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Customer\Model\CustomerRegistry $customerRegistry, \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository, \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\Data\Form\FormKey $formkey, \Magento\Quote\Model\QuoteFactory $quote, \Magento\Quote\Model\QuoteManagement $quoteManagement, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, \Magento\Sales\Model\Service\OrderService $orderService, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Framework\Api\FilterBuilder $filterBuilder, \Icare\Installment\Helper\Data $installmentHelper, \Icare\Cms\Helper\Rating $ratingHelper)
    {
        $this->_storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->addressRepository = $addressRepository;
        $this->customerResourceModel = $customerResourceModel;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
        $this->_productFactory = $productFactory;
        $this->_installmentHelper = $installmentHelper;
        $this->_ratingHelper = $ratingHelper;
    }

    /**
     * function confirmOrder
     * @api
     *
     * @param int $orderId            
     * @return true|false
     * @throws IcareWebApiException
     */
    public function confirmOrder($orderId)
    {
        if (! $this->checkOrderExistedByIncrement($orderId)) {
            $errors[] = new IcareException(__("Order is not existed"));
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        // object order
        $objectOrder = $om->create('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderId);
        if ($order->getStatus() !== \Icare\EventOrder\Plugin\OrderPlugin::ORDER_PENDING ) {
            throw new IcareWebApiException(500, __('Can not confirm order'));
        }
        $result = array();
        // update status order
        $order->setStatus(Order::STATE_PROCESSING);
        $order->setState(Order::STATE_PROCESSING);
        $order->save($order);
        // update client id
        $result[] = true;
        return $result;
        
    }

    /**
     *
     * Confirm order with passcode
     *
     * @param int $orderId
     * @param string $device_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function confirmOrderWithPasscode($orderId, $device_id = false, $note = 'no comment')
    {
        if (!$this->checkOrderExistedByIncrement($orderId)) {
            $errors[] = new IcareException(__("Order is not existed"));
            throw new IcareWebApiException(402, __('Order is not existed'), $errors);
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();


        // object order
        /**@var \Magento\Sales\Model\Order $objectOrder * */
        $objectOrder = $om->create('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderId);
        
        if ($order->getStatus() == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_CANCEL ) {
            throw new IcareWebApiException(500, __('It appears that your order has already been cancelled. If you think this is a mistake, please let us know.'));
        }
        
        if ($order->getStatus() !== \Icare\EventOrder\Plugin\OrderPlugin::ORDER_PENDING ) {
            throw new IcareWebApiException(500, __('It appears that your order has already been confirmed.'));
        }
        $result = array();
        // update status order
        $order->setStatus(Order::STATE_PROCESSING);
        $order->setState(Order::STATE_PROCESSING);
        $order->save($order);
        // update client id
        $result[] = true;
        return $result;
    }

    /**
     *
     * @param
     *            $orderId
     * @return bool
     */
    protected function checkOrderExistedByIncrement($orderId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $om->create('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
        $orders = $collectionFactory->create()
            ->addAddressFields()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('increment_id', $orderId)
            ->load();
        if (count($orders) <= 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @param
     *            $orderId
     * @return bool
     */
    protected function checkOrderExisted($orderId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $om->create('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
        $orders = $collectionFactory->create()
            ->addAddressFields()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('entity_id', $orderId)
            ->load();
        if (count($orders) <= 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getOrderDetailV3($orderIncrementId)
    {
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $objectOrder = $om->get('Magento\Sales\Model\Order');
            $objectProduct = $om->get('Magento\Catalog\Model\Product');
            $iCareHelper = $om->get('Icare\Custom\Helper\ICareHelper');
            $iCareAddressObj = $om->get('Magento\Customer\Model\Address');
            $customerObj = $om->get('Magento\Customer\Model\Customer');
            $websiteObj = $om->get('Magento\Store\Model\Website');

            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('subtotal')
                ->addFieldToSelect('shipping_amount')
                ->addFieldToSelect('saving_account_amount')
                ->addFieldToSelect('tax_amount')
                ->addFieldToFilter('increment_id', $orderIncrementId)
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->setOrder('updated_at', 'desc')
                ->load();
            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $objectProduct->setStoreId($store->getStoreId());
                $orderShippingInfo = $orderInfo->getShippingAddress()->getData();
                $icareAddressId = $orderInfo->getIcareAddressId();

                // Check iCare address
                if (!empty($icareAddressId)) {
                    $iCareAddress = $iCareAddressObj->load($icareAddressId);
                    $iCare = $customerObj->load($iCareAddress->getParentId());
                    $street = $iCareAddress->getStreet();
                }

                $orderStatus = $orderInfo->getStatus();
                $orderData = $order->getData();

                // Check order status : Delivery Failed
                if ($orderStatus == 'delivery_failed') {
                    $shipmentCollection = $order->getShipmentsCollection();
                    foreach ($shipmentCollection as $shipment) {
                        $reasons = $this->getDeliveryFailedReason($shipment->getId());
                    }
                    $orderData['failed_reason'] = $reasons;
                }

                $products = array();
                $taxPercent = 0;
                foreach ($orderInfo->getItemsCollection() as $item) {
                    $taxPercent = $item->getTaxPercent();
                    $product = $objectProduct->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $productItem = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl,
                        'price' => $iCareHelper->getPriceIncludeTax($product, $store),
                        'description' => $product->getDescription(),
                        'installment' => $this->_installmentHelper->getInstallmentByOrder($item),
                        'option' => $this->getOptionValueInSaleItem($item, $product)
                    );

                    $products[] = $productItem;
                }

                $orderData['tax_percent'] = $taxPercent;
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['shipping_info']['street'] = $orderShippingInfo['street'];
                $orderData['shipping_info']['city'] = $orderShippingInfo['city'];
                $orderData['shipping_info']['country_id'] = $orderShippingInfo['country_id'];
                $orderData['shipping_info']['postcode'] = $orderShippingInfo['postcode'];
                $orderData['shipping_info']['telephone'] = $orderShippingInfo['telephone'];
                $orderData['shipping_info']['district'] = $orderShippingInfo['region'];
                $shipping_method = $orderInfo->getShippingMethod();
                if ($shipping_method !== 'flatrate_flatrate') {
                    if (!empty($icareAddressId) && !empty($iCare->getId())) {
                        $website = $websiteObj->load($iCare->getWebsiteId());
                        $orderData['icare_info']['icare_id'] = $iCare->getId();
                        $orderData['icare_info']['icare_center_type'] = $iCare->getIcareCenterType();
                        $orderData['icare_info']['website_id'] = $iCare->getWebsiteId();
                        $orderData['icare_info']['address_id'] = $iCareAddress->getId();
                        $orderData['icare_info']['street'] = reset($street);
                        $orderData['icare_info']['telephone'] = $iCareAddress->getTelephone();
                        $orderData['icare_info']['company'] = $iCareAddress->getCompany();
                        $orderData['icare_info']['district'] = $iCareAddress->getRegion();
                        $orderData['icare_info']['city'] = $iCareAddress->getCity();
                        $orderData['icare_info']['postcode'] = $iCareAddress->getPostcode();
                        $orderData['icare_info']['full_name'] = $iCare->getFirstname() . ' ' . $iCare->getLastname();
                        $orderData['icare_info']['country'] = $website->getName();
                        $orderData['icare_info']['country_code'] = $website->getCode();
                    }
                }

                $orderData['company'] = $orderShippingInfo['company'];
                $orderData['sale_items'] = $products;
                // add client id
                $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                // add more username
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }

                $rating = $this->_ratingHelper->getRatingInfo('sales_order', $order->getId());
                if ($rating) {
                    $orderData['rating_info']['id'] = $rating->getId();
                    $orderData['rating_info']['page_id'] = $rating->getPageId();
                    $orderData['rating_info']['customer_id'] = $rating->getCustomerId();
                    $orderData['rating_info']['rating'] = $rating->getRating();
                    $orderData['rating_info']['data'] = $rating->getData('data');
                    $orderData['rating_info']['creation_time'] = strtotime($rating->getCreationTime());
                }

                $orderDatas[] = $orderData;
            }
            $result = $orderDatas;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $errors[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }
        return $result;
    }

    /**
     *
     * @param
     *            $saleItem
     */
    protected function getOptionValueInSaleItem($saleItem, $product)
    {
        if ($saleItem instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $orderItemObj = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Sales\Model\Order\Item');
            $saleItem = $orderItemObj->load($saleItem->getItemId());
        }
        $productOptions = $saleItem->getProductOptions();
        $options = $product->getOptions();
        $availableOptions = [];
        $returnOptions = [];
        if ($productOptions) {
            if (isset($productOptions['info_buyRequest']['options'])) {
                foreach ($productOptions['info_buyRequest']['options'] as $key => $value) {
                    $availableOptions[] = [
                        'option_id' => $key,
                        'option_value' => $value
                    ];
                }
            } else
                if (isset($productOptions['options'])) {
                    $availableOptions = $productOptions['options'];
                }
        }
        /**@var $option \Magento\Catalog\Model\Product\Option */
        foreach ($options as $option) {
            foreach ($availableOptions as $value) {
                if ($option->getOptionId() == $value['option_id']) {
                    foreach ($option->getValues() as $key => $optionType) {
                        if ($value['option_value'] == $key) {
                            $returnOptions[] = [
                                'option_id' => $option->getOptionId(),
                                'title' => $option->getTitle(),
                                'value' => [
                                    [
                                        'option_type_id' => $optionType->getOptionTypeId(),
                                        'value' => $optionType->getTitle()
                                    ]
                                ]
                            ];
                            break;
                        }
                    }
                }
            }
        }
        return $returnOptions;
    }

    public function getCustomerOrderList(GetOrderListInfoByCustomerInterface $getOrderListInfo)
    {
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->create('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $store = $om->create('Magento\Store\Model\StoreManagerInterface')->getStore();
            $objectOrder = $om->create('Magento\Sales\Model\Order');
            $objectProduct = $om->create('Magento\Catalog\Model\Product');

            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('customer_id')
                ->addFieldToFilter('customer_id', $getOrderListInfo->getCustomerid())
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->setOrder('updated_at', 'desc')
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $shipping = $orderInfo->getShippingAddress();
                if (isset($shipping)) {
                    $orderShippingInfo = $shipping->getData();
                } else {
                    $orderShippingInfo = null;
                }
                // $orderShippingInfo = $orderInfo->getShippingAddress()->getData();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    $product = $objectProduct->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['shipping_info']['street'] = isset($orderShippingInfo) ? $orderShippingInfo['street'] : '';
                $orderData['shipping_info']['city'] = isset($orderShippingInfo) ? $orderShippingInfo['city'] : '';
                $orderData['shipping_info']['country_id'] = isset($orderShippingInfo) ? $orderShippingInfo['country_id'] : '';
                $orderData['shipping_info']['postcode'] = isset($orderShippingInfo) ? $orderShippingInfo['postcode'] : '';
                $orderData['shipping_info']['telephone'] = isset($orderShippingInfo) ? $orderShippingInfo['telephone'] : '';
                $orderData['shipping_info']['district'] = isset($orderShippingInfo) ? $orderShippingInfo['region'] : '';
                $orderData['company'] = isset($orderShippingInfo) ? $orderShippingInfo['company'] : '';
                $orderData['sale_items'] = $products;
                // add client id
                $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderDatas[] = $orderData;
            }
            $result[] = [
                'orders' => $orderDatas
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $errors[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }
        return $result;
    }

    /**
     * Set customer for place order
     */
    public function setCustomerAddress($customer, $addressId = null, $customerAddressId = null, $addressValue = null, $calculate = false, $shippingMethod = null)
    {
        $orderData = array();
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        try {

            if ($shippingMethod == 1) {
                $address = $om->create('Magento\Customer\Model\Address')->load($addressId);
                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
            } elseif ($customerAddressId != null && $customerAddressId > 0) {
                $address = $om->create('Magento\Customer\Model\Address')->load($customerAddressId);
                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
            } elseif ($addressValue) {
                $addressValue = reset($addressValue);
                // set shipping address for customer

                $address = $om->create('Magento\Customer\Model\Address');
                $address->setCustomerId($customer->getId());
                // get country from customer
                if (empty($addressValue->getCountry())) {
                    $country = strtoupper($customer->getStore()->getWebsiteCode());
                } else {
                    $country = strtoupper($addressValue->getCountry());
                }
                $addressValue->setCountry($country);
                $address->setCountryId($country);
                // ////////////
                $address->setStreet(array(
                    $addressValue->getStreet()
                ));
                $address->setPostcode($addressValue->getPostcode());
                if (!empty($addressValue->getTelephone())) {
                    $address->setTelephone($addressValue->getTelephone());
                }
                $address->setCity($addressValue->getCity());
                $address->setFirstname($customer->getFirstname());
                $address->setLastname($customer->getLastname());
                if ($addressValue->getDistrict()) {

                    if (Custom::create()->checkExistRegion(strtoupper($addressValue->getCountry()), $addressValue->getDistrict())) {
                        $region_id = Custom::create()->checkExistRegion(strtoupper($addressValue->getCountry()), $addressValue->getDistrict());
                        $regionObj = $om->create('Magento\Directory\Model\Region')->load($region_id);
                        $region = $regionObj->getDefaultName();
                    } else {
                        $recode = explode(' ', $addressValue->getDistrict());
                        $code = '';
                        foreach ($recode as $co) {
                            $code .= substr($co, 1);
                        }
                        $code = strtoupper($code);
                        $region = $om->create('Magento\Directory\Model\Region');
                        $region->setCountryId(strtoupper($addressValue->getCountry()));
                        $region->setCode($code);
                        $region->setDefaultName($addressValue->getDistrict());
                        $region->save();
                        $region_id = $region->getRegionid();
                    }
                    $address->setRegionId($region_id);
                } else {
                    $address->setRegionId(0);
                }
                $address->setIsDefaultShipping(1);
                $address->setIsDefaultBilling(1);
                $address->save();

                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
            } else {
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if (!$address) {
                    $addresses = $customer->getAddresses();
                    $address = reset($addresses);
                }

                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
            }

            $orderData = [
                'email' => $customer->getEmail(), // buyer email id
                'shipping_address' => [
                    'firstname' => $customer->getFirstname(), // address Details
                    'lastname' => $customer->getLastname(),
                    'street' => $street,
                    'city' => $address->getCity(),
                    'country_id' => $address->getCountryId(),
                    'region' => $region,
                    'postcode' => $address->getPostcode(),
                    'telephone' => $address->getTelephone(),
                    // save address to book
                    'save_in_address_book' => 0
                ]
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $errors[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }
        return $orderData;
    }

    /**
     * Set customer for place order
     */
    public function setCustomerAddressCalculate($customer, $addressId = null, $customerAddressId = null, $addressValue = null, $calculate = false, $shippingMethod = null)
    {
        $orderData = array();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $streetDetail = '';
        $city = '';
        $country = '';
        $regionDetail = '';
        $postcode = '';
        $telephone = '';

        try {
            if ($addressValue) {
                $addressValue = reset($addressValue);
                if (empty($addressValue->getCountry())) {
                    $country = strtoupper($customer->getStore()->getWebsiteCode());
                } else {
                    $country = strtoupper($addressValue->getCountry());
                }
                $addressValue->setCountry($country);
                if ($addressValue->getDistrict()) {

                    if (Custom::create()->checkExistRegion(strtoupper($addressValue->getCountry()), $addressValue->getDistrict())) {
                        $region_id = Custom::create()->checkExistRegion(strtoupper($addressValue->getCountry()), $addressValue->getDistrict());
                        $region = $om->create('Magento\Directory\Model\Region')->load($region_id);
                    } else {
                        $recode = explode(' ', $addressValue->getDistrict());
                        $code = '';
                        foreach ($recode as $co) {
                            $code .= substr($co, 1);
                        }
                        $code = strtoupper($code);
                        $region = $om->create('Magento\Directory\Model\Region');
                        $region->setCountryId(strtoupper($addressValue->getCountry()));
                        $region->setCode($code);
                        $region->setDefaultName($addressValue->getDistrict());
                        $region->save();
                    }
                }
                $streetDetail = $addressValue->getStreet();
                $city = $addressValue->getCity();
                $country = $addressValue->getCountry();
                $regionDetail = $region->getDefauktName();
                $postcode = $addressValue->getPostcode();
                $telephone = $addressValue->getTelephone();
            } elseif ($shippingMethod == 1) {
                $address = $om->create('Magento\Customer\Model\Address')->load($addressId);
                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
                $streetDetail = $street;
                $city = $address->getCity();
                $country = $address->getCountryId();
                $postcode = $address->getPostcode();
                $telephone = $address->getTelephone();
                $regionDetail = $region;
            } elseif ($customerAddressId != null && $customerAddressId > 0) {
                $address = $om->create('Magento\Customer\Model\Address')->load($customerAddressId);
                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
                $streetDetail = $street;
                $city = $address->getCity();
                $country = $address->getCountryId();
                $postcode = $address->getPostcode();
                $telephone = $address->getTelephone();
                $regionDetail = $region;
            } else {
                $address = $customer->getDefaultShippingAddress();
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }
                if (!$address) {
                    $addresses = $customer->getAddresses();
                    $address = reset($addresses);
                }

                $street = $address->getStreet();
                $street = reset($street);
                $region = $address->getRegion();
                $streetDetail = $street;
                $city = $address->getCity();
                $country = $address->getCountryId();
                $postcode = $address->getPostcode();
                $telephone = $address->getTelephone();
                $regionDetail = $region;
            }

            $orderData = [
                'email' => $customer->getEmail(), // buyer email id
                'shipping_address' => [
                    'firstname' => $customer->getFirstname(), // address Details
                    'lastname' => $customer->getLastname(),
                    'street' => $streetDetail,
                    'city' => $city,
                    'country_id' => $country,
                    'region' => $regionDetail,
                    'postcode' => $postcode,
                    'telephone' => $telephone,
                    // save address to book
                    'save_in_address_book' => 0
                ]
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $errors[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }
        return $orderData;
    }

    /**
     * Create order
     */
    public function CreatePendingOrder($customer, $orderData, $itemID, $qty)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote
        $product = $this->_product->create()
            ->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $product->setPrice($product->getPrice());
            $quote->addProduct($product, intval($qty));

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready
            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();

            // check credit limit
            $total_order = (($product->getPrice()) * $qty);
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId());
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set no send email
            // $order->setEmailSent(0);
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Create order V2
     */
    public function CreatePendingOrderV2($customer, $orderData, $itemID, $qty, $itemInstallment = null)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $items = array(
                $itemID,
                $itemInstallment
            );
            foreach ($items as $item) {
                $product = $this->_productFactory->create()
                    ->setStoreId($customer->getStoreId())
                    ->load($item);
                $product->setPrice($product->getPrice());
                if (!$product->canConfigure()) {
                    $quote->addProduct($product, intval($qty));
                    $quote->save();
                } else {

                    // TODO: Must be pass from mobile app
                    $options = $product->getOptions();
                    $optionIds = array();
                    $optionIdWithValue = array();
                    foreach ($options as $option) {
                        $optionIds[] = $option->getOptionId();
                        foreach ($option->getValues() as $optionType) {
                            $optionIdWithValue[$option->getOptionId()] = $optionType->getOptionTypeId();
                        }
                    }

                    $om = \Magento\Framework\App\ObjectManager::getInstance();
                    $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
                    $quoteItem = $itemFactory->create();
                    $quoteItem->setProduct($product);
                    $quoteItem->setPrice($product->getPrice());
                    $quoteItem->setQty(intval($qty));
                    $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                        'product' => $product,
                        'code' => 'option_ids',
                        'value' => implode(',', $optionIds)
                    )));
                    foreach ($optionIdWithValue as $optionId => $optionTypeId) {
                        $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                            'product' => $product,
                            'code' => 'option_' . $optionId,
                            'value' => $optionTypeId
                        )));
                    }
                    $quoteItem->setQuote($quote);
                    $quote->addItem($quoteItem);
                    $quote->save();
                }
            }

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();

            // check credit limit
            $total_order = $product->getPrice() * intval($qty);
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId());
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set no send email
            // $order->setEmailSent(0);
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Update order to save userId
     */
    public function updateOrder($order, $userId)
    {
        try {
            // Load object order update
            $update_order = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\Order');
            // update uid for order
            $update_order = $update_order->load($order->getEntityId());
            $update_order->setUserId($userId);
            $update_order->save($update_order);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * shipping method for place order
     */
    public function placeOrderAddShippingMethod($address, $quote, $shippingMethod = null)
    {
        try {
            if ($shippingMethod == 1) {
                $method = 'freeshipping_freeshipping';
            } elseif ($shippingMethod == 2) {
                $method = 'flatrate_flatrate';
            } else {
                $method = 'freeshipping_freeshipping';
            }
            $quote->getBillingAddress()->addData($address);
            $quote->getShippingAddress()->addData($address);
            // Collect Rates and Set Shipping & Payment Method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($method); // shipping method
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Set payment method
     */
    public function placeOrderSetPaymentMethod($quote, $method = 'checkmo')
    {
        $quote->setPaymentMethod($method); // payment method
    }

    /**
     * Get client id for customer
     *
     * @param $customerId return
     *            $client_id
     */
    public function getClientIdCustomer($customerId)
    {
        if (Custom::create()->getClientIdCustomer($customerId)) {
            return Custom::create()->getClientIdCustomer($customerId);
        } else {
            return null;
        }
    }

    /**
     * Payment import data
     *
     * @param string $method
     */
    public function paymentImportData($quote, $method = 'checkmo')
    {
        $quote->getPayment()->importData([
            'method' => $method
        ]);
    }

    /**
     * Get Order List By User Id
     *
     * @param GetOrderListInfoInterface $getOrderListInfo
     * @return mixed
     */
    public function getOrderListV2(GetOrderListInfoInterface $getOrderListInfo)
    {
        // Example Json request: {"getOrderInfo": {"userid":"1", "pagesize":"1", "pagenum":"1"}}
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->create('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $store = $om->create('Magento\Store\Model\StoreManagerInterface')->getStore();
            $objectOrder = $om->create('Magento\Sales\Model\Order');
            $objectProduct = $om->create('Magento\Catalog\Model\Product');
            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToFilter('user_id', $getOrderListInfo->getUserid())
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->setOrder('updated_at', 'desc')
                ->setPage($getOrderListInfo->getPagenum(), $getOrderListInfo->getPagesize())
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    $product = $objectProduct->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                // /////////////////
                $orderDatas[] = $orderData;
            }

            $isLastPage = false;
            if (count($orderDatas) < $getOrderListInfo->getPagesize()) {
                $isLastPage = true;
            }
            $result[] = [
                'orders' => $orderDatas,
                'isLastPage' => $isLastPage
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    /**
     * @Icare\Cache\Annotation\Cacheable(cacheName="orders")
     *
     * @param string $userId
     * @return mixed
     */
    public function getOrderListByUserId($userId)
    {
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $bashUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $objectOrder = $om->get('Magento\Sales\Model\Order');
            $dateOneMonthRange = $collectionReport->getDateRange('30d', null, null);

            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToFilter('user_id', $userId)
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->addFieldToFilter('updated_at', $dateOneMonthRange)
                ->setOrder('updated_at', 'desc')
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    // $product = Custom::create()->customProductLoad($item->getProductId());
                    $product = $om->create('Magento\Catalog\Model\Product')
                        ->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                if ($orderData['customer_id']) {
                    $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                }
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderData['organization_id'] = $this->getOrganizationId($orderData['customer_id']);
                // /////////////////
                $orderDatas[] = $orderData;
            }
            $result[] = [
                'orders' => $orderDatas
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    /**
     * @Icare\Cache\Annotation\Cacheable(cacheName="orders")
     *
     * @param string $userId
     * @param string $from
     * @param string $to
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByUserIdFromTo($userId, $from = null, $to = null, $pageNum = null, $pageSize = null)
    {
        if ($from == null && $to == null && $pageNum == null && $pageSize == null) {
            return $this->getOrderListByUserId($userId);
        }
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $bashUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $objectOrder = $om->get('Magento\Sales\Model\Order');

            $dateFrom = $this->convertTimeZone($from);
            $dateTo = $this->convertTimeZone($to);

            $dateOneMonthRange = $collectionReport->getDateRange('custom', $dateFrom, $dateTo);

            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToFilter('user_id', $userId)
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->addFieldToFilter('created_at', $dateOneMonthRange)
                ->setOrder('updated_at', 'desc')
                ->setPage($pageNum, $pageSize)
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    // $product = Custom::create()->customProductLoad($item->getProductId());
                    $product = $om->create('Magento\Catalog\Model\Product')
                        ->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                if ($orderData['customer_id']) {
                    $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                }
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderData['organization_id'] = $this->getOrganizationId($orderData['customer_id']);
                // /////////////////
                $orderDatas[] = $orderData;
            }
            $isLastPage = false;
            if (count($orderDatas) < $pageSize) {
                $isLastPage = true;
            }
            $result[] = [
                'orders' => $orderDatas,
                'isLastPage' => $isLastPage
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    /**
     * @Icare\Cache\Annotation\Cacheable(cacheName="customer_order")
     *
     * @param string $customerId
     * @param string $form
     * @param string $to
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByCustomerIdFromTo($customerId, $from, $to, $pageNum, $pageSize)
    {
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $bashUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $objectOrder = $om->get('Magento\Sales\Model\Order');
            $dateFrom = $this->convertTimeZone($from);
            $dateTo = $this->convertTimeZone($to);
            $dateOneMonthRange = $collectionReport->getDateRange('30d', $dateFrom, $dateTo);

            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('customer_id')
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ])
                ->addFieldToFilter('updated_at', $dateOneMonthRange)
                ->setOrder('updated_at', 'desc')
                ->setPage($pageNum, $pageSize)
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    // $product = Custom::create()->customProductLoad($item->getProductId());
                    $product = $om->create('Magento\Catalog\Model\Product')
                        ->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                if ($orderData['customer_id']) {
                    $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                }
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderData['organization_id'] = $this->getOrganizationId($orderData['customer_id']);
                // /////////////////
                $orderDatas[] = $orderData;
            }
            $isLastPage = false;
            if (count($orderDatas) < $pageSize) {
                $isLastPage = true;
            }
            $result[] = [
                'orders' => $orderDatas,
                'isLastPage' => $isLastPage
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    /**
     * @Icare\Cache\Annotation\Cacheable(cacheName="customer_order")
     *
     * @param string $customerId
     * @param string $form
     * @param string $to
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByCustomerIdV4($customerId, $pageNum = null, $pageSize = null)
    {
        $result = array();
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            // object order
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $bashUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $objectOrder = $om->get('Magento\Sales\Model\Order');
            $orders = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('customer_id')
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ]);
            if ($pageNum == null || $pageSize == null) {
                $dateOneMonthRange = $collectionReport->getDateRange('30d', null, null);
                $orders->addFieldToFilter('updated_at', $dateOneMonthRange);
            } else {
                $orders->setPage($pageNum, $pageSize);
            }

            $orders = $orders->setOrder('updated_at', 'desc')
                ->setPage($pageNum, $pageSize)
                ->load();

            $orderDatas = array();
            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = array();
                foreach ($orderInfo->getItemsCollection() as $item) {
                    // $product = Custom::create()->customProductLoad($item->getProductId());
                    $product = $om->create('Magento\Catalog\Model\Product')
                        ->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    );

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                if ($orderData['customer_id']) {
                    $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                }
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderData['organization_id'] = $this->getOrganizationId($orderData['customer_id']);
                // /////////////////
                $orderDatas[] = $orderData;
            }
            $isLastPage = false;
            if (count($orderDatas) < $pageSize) {
                $isLastPage = true;
            }
            $result[] = [
                'orders' => $orderDatas,
                'isLastPage' => $isLastPage
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    /**
     * convert to date with timezone UTC
     */
    public function convertTimeZone($timeString)
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s', $timeString);
        return $date;
    }

    private $_action_mapping = [
        'ready_to_ship' => 'actionReadyToShip',
        'canceled' => 'actionCancel',
        'payment_disbursed' => 'actionPaymentReceived',
        'ready_payment' => 'actionAllMoneyCollected',
        'confirm' => 'confirmOrderWithPasscode'
    ];

    /**
     *
     * @param string $orderNo
     * @param string $action
     * @param string $note
     * @return mixed
     * @throws Exception
     */
    public function actionOrder($orderNo, $action, $note = 'no comment')
    {
        $result = array();
        try {
            $this->_logger->info(sprintf('[orderNo=%s, action=%s, note=%s]', $orderNo, $action, $note));
            $call_function = $this->_action_mapping[$action];
            if (call_user_func(array(
                $this,
                $call_function
            ), $orderNo, $note)) {
                $result[] = [
                    'orderNo' => $orderNo,
                    'action' => $action,
                    'is_updated' => true,
                    'note' => ''
                ];
            } else {
                $result[] = [
                    'orderNo' => $orderNo,
                    'action' => $action,
                    'is_updated' => false,
                    'note' => __('order is not existed')
                ];
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            if ($ex instanceof \Magento\Framework\Webapi\Exception) throw $ex;
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
        return $result;
    }

    private function actionReadyToShip($orderNo, $note)
    {
        /**
         *
         * @var \Magento\Sales\Model\Order $objectOrder
         */
        $objectOrder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderNo);

        if (!$order->getId()) {
            throw new Magento\Framework\Exception\NoSuchEntityException(__('Invalid Order Increment ID: ' . $orderNo));
        }

        /**
         *
         * @var \Icare\IcareOrderApi\Model\OrderShipment $orderShipment
         */
        $orderShipment = \Magento\Framework\App\ObjectManager::getInstance()->get('Icare\IcareOrderApi\Model\OrderShipment');
        $shipment = $orderShipment->generateShipment($order);
        $this->_logger->info('created shipment ' . $shipment->getIncrementId() . ' for order ' . $order->getIncrementId());

        return true;
    }

    private function actionCancel($orderNo, $note)
    {
        /**
         *
         * @var \Magento\Sales\Model\Order $objectOrder
         */
        $objectOrder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderNo);
        if ($order->getId()) {
            if ($order->getStatus() == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_CANCEL) {
                throw new IcareException(__('It appears that your order has already been cancelled.'));
            }
            
            if ($order->getStatus() == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_CONFIRMED) {
                throw new IcareException(__('It appears that your order has already been confirmed. If you like to cancel this order, please create a ticket for us.'));
            }
            
            if ($order->getStatus() !== \Icare\EventOrder\Plugin\OrderPlugin::ORDER_PENDING) {
                throw new IcareException(__('It appears that your order has already been confirmed. If you like to cancel this order, please create a ticket for us.'));
            }
            if ($order->canCancel()) {
                $order->cancel();

                $order->setStatus('canceled');
                $order->setState('canceled');
                $order->addStatusHistoryComment($note);
                $order->save();

            } else {
                $order->setStatus('canceled');
                $order->setState('canceled');
                $order->addStatusHistoryComment($note);
                $order->save();

                $eventManager = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Event\ManagerInterface');
                $eventManager->dispatch('order_cancel_after', ['order' => $order]);
            }
            return true;
        } else {
            return false;
        }
    }

    private function actionPaymentReceived($orderNo, $note)
    {
        /**
         *
         * @var \Magento\Sales\Model\Order $objectOrder
         */
        $objectOrder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderNo);
        if ($order->getId()) {
            $order->setStatus('payment_recieved');
            $order->addStatusHistoryComment($note);
            $order->save();
            return true;
        } else {
            return false;
        }
    }

    private function actionAllMoneyCollected($orderNo, $note)
    {
        /**
         *
         * @var \Magento\Sales\Model\Order $objectOrder
         */
        $objectOrder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order');
        $order = $objectOrder->loadByIncrementId($orderNo);
        if ($order->getId()) {
            $order->setStatus('all_money_collected');
            $order->addStatusHistoryComment($note);
            $order->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create order V3
     */
    public function CreatePendingOrderV3($customer, $orderData, $itemID, $qty, $itemInstallment = null, $savingAccount = 0, $savingAmount = 0)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            // $product = $this->_productFactory->create()->setStoreId($customer->getStoreId())->load($itemID);
            $product->setPrice($product->getPrice());
            if (!$product->canConfigure()) {
                $quote->addProduct($product, intval($qty));
                $quote->save();
            } else {
                // TODO: Must be pass from mobile app
                $options = $product->getOptions();
                $optionIds = array();
                $optionIdWithValue = array();
                foreach ($options as $option) {
                    $optionIds[] = $option->getOptionId();
                    foreach ($option->getValues() as $optionType) {
                        $optionIdWithValue[$option->getOptionId()] = $optionType->getOptionTypeId();
                    }
                }

                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
                $quoteItem = $itemFactory->create();
                $quoteItem->setProduct($product);
                $quoteItem->setPrice($product->getPrice());
                $quoteItem->setQty(intval($qty));
                $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                    'product' => $product,
                    'code' => 'option_ids',
                    'value' => implode(',', $optionIds)
                )));
                foreach ($optionIdWithValue as $optionId => $optionTypeId) {
                    $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                        'product' => $product,
                        'code' => 'option_' . $optionId,
                        'value' => $optionTypeId
                    )));
                }
                $quoteItem->setQuote($quote);
                $quote->addItem($quoteItem);
                $quote->save();
            }

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();

            // check credit limit
            $total_order = $product->getPrice() * intval($qty);
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId(), $savingAccount, $savingAmount);
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set no send email
            // $order->setEmailSent(0);
            if ($savingAccount == 1) {
                $order->setSavingAccount(1);
            }
            if ($savingAmount > 0) {
                $order->setSavingAccountAmount($savingAmount);
            }
            $order->save();
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Create order V4
     */
    public function CreatePendingOrderV4($customer, $orderData, $itemID, $qty, $itemInstallment = null, $optionValues = null)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $product->setPrice($product->getPrice());
            if (!$product->canConfigure()) {
                $quote->addProduct($product, intval($qty));
                $quote->save();
            } else {

                $options = $product->getOptions();
                $optionIds = array();
                foreach ($options as $option) {
                    $optionIds[] = $option->getOptionId();
                }

                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
                $quoteItem = $itemFactory->create();
                $quoteItem->setProduct($product);
                $quoteItem->setPrice($product->getPrice());
                $quoteItem->setQty(intval($qty));
                $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                    'product' => $product,
                    'code' => 'option_ids',
                    'value' => implode(',', $optionIds)
                )));
                if ($optionValues) {
                    foreach ($optionValues as $option) {
                        $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                            'product' => $product,
                            'code' => 'option_' . $option->getId(),
                            'value' => $option->getValue()
                        )));
                    }
                }

                $quoteItem->setQuote($quote);
                $quote->addItem($quoteItem);
                $quote->save();
            }

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();

            // check credit limit
            $total_order = $product->getPrice() * intval($qty);
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId());
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set no send email
            // $order->setEmailSent(0);
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Create order V4
     */
    public function CreatePendingOrderV5($customer, $orderData, $itemID, $qty, $itemInstallment = null, $optionValues = null, $savingAccount = 0, $savingAmount = 0)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $product->setPrice($product->getPrice());
            if (!$product->canConfigure()) {
                $quote->addProduct($product, intval($qty));
                $quote->save();
            } else {

                $options = $product->getOptions();
                $optionIds = array();
                foreach ($options as $option) {
                    $optionIds[] = $option->getOptionId();
                }

                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
                $quoteItem = $itemFactory->create();
                $quoteItem->setProduct($product);
                $quoteItem->setPrice($product->getPrice());
                $quoteItem->setQty(intval($qty));
                $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                    'product' => $product,
                    'code' => 'option_ids',
                    'value' => implode(',', $optionIds)
                )));
                if ($optionValues) {
                    foreach ($optionValues as $option) {
                        $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                            'product' => $product,
                            'code' => 'option_' . $option->getId(),
                            'value' => $option->getValue()
                        )));
                    }
                }

                $quoteItem->setQuote($quote);
                $quote->addItem($quoteItem);
                $quote->save();
            }

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote

            // check credit limit
            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
            $total = $quote->toArray();

            $total_order = $total['grand_total'];
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId(), $savingAccount, $savingAmount);
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set no send email
            // $order->setEmailSent(0);
            if ($savingAccount == 1) {
                $order->setSavingAccount(1);
            }
            if ($savingAmount > 0) {
                $order->setSavingAccountAmount($savingAmount);
            }

            $order->save();
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Create order V6
     */
    public function CreatePendingOrderV6($customer, $orderData, $itemID, $qty, $itemInstallment = null, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $addressId = 0, $customerAddressId = 0, $shippingMethod = null)
    {

        // get store
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $product->setPrice($product->getPrice());
            if (!$product->canConfigure()) {
                $quote->addProduct($product, intval($qty));
                $quote->save();
            } else {

                $options = $product->getOptions();
                $optionIds = array();
                foreach ($options as $option) {
                    $optionIds[] = $option->getOptionId();
                }

                $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
                $quoteItem = $itemFactory->create();
                $quoteItem->setProduct($product);
                $quoteItem->setPrice($product->getPrice());
                $quoteItem->setQty(intval($qty));
                $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                    'product' => $product,
                    'code' => 'option_ids',
                    'value' => implode(',', $optionIds)
                )));
                if ($optionValues) {
                    foreach ($optionValues as $option) {
                        $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                            'product' => $product,
                            'code' => 'option_' . $option->getId(),
                            'value' => $option->getValue()
                        )));
                    }
                }

                $quoteItem->setQuote($quote);
                $quote->addItem($quoteItem);
                $quote->save();
            }

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote, $shippingMethod);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
            $total = $quote->toArray();

            // check credit limit
            $total_order = $total['grand_total'];
            Custom::create()->checkCreditDueLimit($total_order, $customer->getId(), $savingAccount, $savingAmount, $itemID, $customer->getStoreId());

            // set no send email
            // $order->setEmailSent(0);
            if ($savingAccount == 1) {
                $quote->setData('saving_account', 1);
                if ($savingAmount != null) {
                    $quote->setData('saving_account_amount', $savingAmount);
                    if ($savingAmount > $total_order) {
                        $quote->setData('saving_account_amount', $total_order);
                    }
                }
            }

            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);

            // update iCare Address
            /**
             *
             * @var \Magento\Customer\Model\Address $address
             */
            $address = $om->create('Magento\Customer\Model\Address')->load($addressId);
            if (!$address->getId() || empty($address->getLocationId())) {
                throw new IcareWebApiException(402, 'iCare Address not found: ' . $addressId);
            }
            $icarecenterId = $address->getParentId();
            $addressType = Custom::create()->getIcareCenterType($icarecenterId);
            if (empty($addressType)) {
                throw new IcareWebApiException(402, 'iCare Address is invalid: ' . $addressId);
            }
            $order->setIcareAddressId($addressId);
            $order->setIcareAddressType($addressType);

            $order->save();
            return $order;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * Create order
     *
     * @param int $customerID
     * @param int $itemID
     * @param int $qty
     * @param int $userId
     * @param int $itemInstallment
     * @param Icare\IcareOrderApi\Api\Data\OptionValueInterface[] $optionValues
     * @param string $savingAccount
     * @param string $savingAmount
     * @param int $addressIcareId
     * @param int $customerAddressId
     * @param Icare\IcareOrderApi\Api\Data\AddressValueInterface[] $addressValue
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    public function placeOrderV6($customerID, $itemID, $qty, $userId, $itemInstallment, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $addressIcareId = null, $customerAddressId = null, $addressValue = null, $shippingMethod = null)
    {
        $customerobject = $this->customerFactory->create();
        $customer = $customerobject->load($customerID);
        if (is_null($customer->getEmail())) {
            $result[] = new IcareException(__("Customer Not Found."));
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }

        // check saving account and amount, saving saving amount to temp table
        // if ($savingAmount > 0 && $savingAccount == 1) {
        // Custom::create()->addTempSaving($customerID, $savingAmount);
        // }

        // @todo: set customer add address and load data for order
        $orderData = $this->setCustomerAddress($customer, $addressIcareId, $customerAddressId, $addressValue, $shippingMethod);
        // Setup and create order
        $order = $this->CreatePendingOrderV6($customer, $orderData, $itemID, $qty, $itemInstallment, $optionValues, $savingAccount, $savingAmount, $addressIcareId, $customerAddressId, $shippingMethod);
        
        try {
            // update order uid
            $this->updateOrder($order, $userId);
            // take order id
            $orderId = $order->getEntityId();

            if ($orderId) {
                $result['orderInfo'] = [
                    'order_id' => $orderId,
                    'increment_id' => $order->getRealOrderId()
                ];
            }
            return $result;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            if ($ex instanceof IcareWebApiException)
                throw $ex;
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    /**
     * Calculate Total Amount Info
     */
    private function calculateTotalAmountInfo($customer, $orderData, $itemID, $qty, $itemInstallment = null, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $shippingMethod = null)
    {
        // get store
        $store = $this->_storeManager->getStore($customer->getStoreId());
        $quote = $this->quote->create(); // Create object of quote
        $quote->setStore($store); // set store for which you create quote

        $quote->setCurrency(); // set currency
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->assignCustomer($customer); // Assign quote to custome
        // add items in quote

        $product = $this->_product->setStoreId($customer->getStoreId())
            ->load($itemID);

        if (is_null($product->getSku())) {
            $error[] = new IcareException(__('Product item not found in the system. Please choose other product.'));
            throw new IcareWebApiException(402, __('Product item not found in the system. Please choose other product.'), $error);
        }
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $itemFactory = $om->create('\Magento\Quote\Model\Quote\ItemFactory');
            $quoteItem = $itemFactory->create();
            $quoteItem->setProduct($product);
            $quoteItem->setPrice($product->getPrice());
            $quoteItem->setQty(intval($qty));

            if ($optionValues) {
                $options = $product->getOptions();
                $optionIds = array();
                foreach ($options as $option) {
                    $optionIds[] = $option->getOptionId();
                }

                $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                    'product' => $product,
                    'code' => 'option_ids',
                    'value' => implode(',', $optionIds)
                )));
                if ($optionValues) {
                    foreach ($optionValues as $option) {
                        $quoteItem->addOption(new \Magento\Framework\DataObject(array(
                            'product' => $product,
                            'code' => 'option_' . $option->getId(),
                            'value' => $option->getValue()
                        )));
                    }
                }
            }

            $quoteItem->setQuote($quote);
            $quote->addItem($quoteItem);
            $quote->save();

            // Set Address to quote
            $this->placeOrderAddShippingMethod($orderData['shipping_address'], $quote, $shippingMethod);
            // set payment method
            $this->placeOrderSetPaymentMethod($quote);
            // no effect inventory
            $quote->setInventoryProcessed(false);
            $quote->save(); // Now Save quote and your quote is ready

            // Set Sales Order Payment
            $this->paymentImportData($quote);
            // Collect Totals & Save Quote
            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
            $total = $quote->toArray();
            if ($shippingMethod == 1) {
                $total['shipping_amount'] = 0;
            }
            return [
                'grand_total' => $total['grand_total'],
                'shipping_amount' => $total['shipping_amount'],
                'subtotal_with_discount' => $total['subtotal_with_discount']
            ];
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $code[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $code);
        }
    }

    /**
     * calculate order information
     *
     * @param int $customerID
     * @param int $itemID
     * @param int $qty
     * @param int $userId
     * @param int $itemInstallment
     * @param Icare\IcareOrderApi\Api\Data\OptionValueInterface[] $optionValues
     * @param string $savingAccount
     * @param string $savingAmount
     * @param int $addressIcareId
     * @param int $customerAddressId
     * @param Icare\IcareOrderApi\Api\Data\AddressValueInterface[] $addressValue
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    public function calculateOrderInfo($customerID, $itemID, $qty, $userId, $itemInstallment, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $addressIcareId = null, $customerAddressId = null, $addressValue = null, $shippingMethod = null)
    {

        // TODO: please pass param shipping method
        if ($shippingMethod == null) {
            $shippingMethod = 2; // delivery with flat_rate
        }

        $customerobject = $this->customerFactory->create();
        $customer = $customerobject->load($customerID);
        if (is_null($customer->getEmail())) {
            $result[] = new IcareException(__("Customer Not Found."));
            throw new IcareWebApiException(401, __('Web Api Internal Error'), $result);
        }

        // @todo: set customer add address and load data for order
        $orderData = $this->setCustomerAddressCalculate($customer, $addressIcareId, $customerAddressId, $addressValue, true, $shippingMethod);
        // Setup and create order

        try {
            $result = [
                'total' => $this->calculateTotalAmountInfo($customer, $orderData, $itemID, $qty, $itemInstallment, $optionValues, $savingAccount, $savingAmount, $shippingMethod)
            ];
            return $result;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    protected function getDeliveryFailedReason($shipmentId)
    {
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         *
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipmentId)
            ->where('delivery_failed_reason is not null');
        $rows = $connection->fetchAssoc($select);
        $reasons = array();
        foreach ($rows as $row) {
            $reasons[] = $row['reason_detail'];
        }

        return $reasons;
    }

    protected function getOrganizationId($customerId) {
        if (empty($customerId)) {
            return null;
        }

        /**
         * @var \Magento\Customer\Model\Customer $customer
         */
        $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Customer')->load($customerId);

        if ($customer) {
            return $customer->getData('organization_id');
        }
        return null;

    }

    /**
     * @param \Icare\IcareOrderApi\Api\Data\GetOrderListInfoInterface $getOrderListInfo
     * @return array
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function getOrderList(GetOrderListInfoInterface $getOrderListInfo)
    {
        // Example Json request: {"getOrderInfo": {"userId":"1", "from":"timestamp", "to":"timestamp", "status": {"pending", "confirmed"}, "organizationId":"1", "pageSize":"1", "pageNum":"1"}}
        $result = [];
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $collectionFactory = $om->get('\Magento\Reports\Model\ResourceModel\Order\CollectionFactory');
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $objectOrder = $om->get('Magento\Sales\Model\Order');
            $collectionCustomer = $om->get('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
            $customers = [];

            /** @var \Magento\Reports\Model\ResourceModel\Order\Collection $collection */
            $collection = $collectionFactory->create()
                ->addAddressFields()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('status')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('updated_at')
                ->addFieldToSelect('user_id')
                ->addFieldToSelect('loan_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToFilter('status', [
                    'nin' => $this->excludeOrderStatus
                ]);

            if (!empty($getOrderListInfo->getUserId())) {
                $collection->addFieldToFilter('user_id', $getOrderListInfo->getUserId());
            }
            if (!empty($getOrderListInfo->getOrganizationId())) {
                $customers = Custom::create()->getCustomerByOrganization($getOrderListInfo->getOrganizationId(), $collectionCustomer);
                if (!empty($customers)) {
                    $collection->addFieldToFilter('customer_id', ['in' => array_keys($customers)]);
                }
            }
            if (!empty($getOrderListInfo->getStatus())) {
                $collection->addFieldToFilter('status', ['in' => $getOrderListInfo->getStatus()]);
            }
            if (!empty($getOrderListInfo->getFrom()) && !empty($getOrderListInfo->getTo())) {
                $dateFrom = $this->convertTimeZone($getOrderListInfo->getFrom());
                $dateTo = $this->convertTimeZone($getOrderListInfo->getTo());
                $dateOneMonthRange = $collectionReport->getDateRange('custom', $dateFrom, $dateTo);
                $collection->addFieldToFilter('created_at', $dateOneMonthRange);
            }
            if (!empty($getOrderListInfo->getPageNum()) && !empty($getOrderListInfo->getPageSize())) {
                $collection->setPage($getOrderListInfo->getPageNum(), $getOrderListInfo->getPageSize());
            }

            $collection->setOrder('updated_at', 'desc');
            $orders = $collection->load();
            $orderDatas = [];

            foreach ($orders as $order) {
                $orderInfo = $objectOrder->load($order['entity_id']);
                $store = $orderInfo->getStore();
                $products = [];
                foreach ($orderInfo->getItemsCollection() as $item) {
                    // $product = Custom::create()->customProductLoad($item->getProductId());
                    $product = $om->create('Magento\Catalog\Model\Product')
                        ->setStoreId($store->getStoreId())
                        ->load($item->getProductId());
                    $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

                    $item = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'image' => $imageUrl
                    ];

                    $products[] = $item;
                }
                $orderData = $order->getData();
                $orderData['created_at'] = strtotime($orderData['created_at']);
                $orderData['updated_at'] = strtotime($orderData['updated_at']);
                $orderData['sale_items'] = $products;
                // add client id
                if ($orderData['customer_id']) {
                    if (!empty($customers)) {
                        $orderData['client_id'] = $customers[$orderData['customer_id']]->getClientId();
                    } else {
                        $orderData['client_id'] = $this->getClientIdCustomer($orderData['customer_id']);
                    }
                }
                // add username sale man
                if ($orderData['user_id'] > 0) {
                    $user = $om->get('Magento\User\Model\User')->load($orderData['user_id']);
                    $orderData['sale_man'] = $user->getName();
                }
                $orderDatas[] = $orderData;
            }

            $data = [
                'orders' => $orderDatas,
            ];

            if (!empty($getOrderListInfo->getPageSize())) {
                $isLastPage = false;
                if (count($orderDatas) < $getOrderListInfo->getPageSize()) {
                    $isLastPage = true;
                }
                $data['isLastPage'] = $isLastPage;
            }
            $result[] = $data;

        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            throw new IntegrationException(__($ex->getMessage()));
        }

        return $result;
    }
}

