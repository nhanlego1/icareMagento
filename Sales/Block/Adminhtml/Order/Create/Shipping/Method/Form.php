<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create shipping method form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';
    const FREESHIPPING = 'Pickup';
    const FLATRATE = 'Delivery';
    /**
     * Shipping rates
     *
     * @var array
     */
    protected $_rates;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     *
     * @var int
     */
    protected $icareAddressId;

    /**
     *
     * @var int
     */
    protected $icareAddressType;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_taxData = $taxData;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->_init();
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_shipping_method_form');
    }

    /**
     * _init function
     * Get address id & address type
     */
    public function _init() {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $session =  $om->get('Magento\Backend\Model\Session\Quote');
        $order = $session->getOrder();
        if($order){
            $this->icareAddressId = $order->getData('icare_address_id');
            $this->icareAddressType = $order->getData('icare_address_type');
        }
    }

    /**
     * Retrieve quote shipping address model
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->_rates = $this->getAddress()->getGroupedAllShippingRates();
        }
        return $this->_rates;
    }

    /**
     * Rertrieve carrier name from store configuration
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($carrierCode == 'freeshipping') {
            return __(self::FREESHIPPING);
        }
        elseif ($carrierCode == 'flatrate') {
            return __(self::FLATRATE);
        }

        return $carrierCode;
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param string $code
     * @return bool
     */
    public function isMethodActive($code)
    {
        return $code === $this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate|false
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get rate request
     *
     * @return mixed
     */
    public function getIsRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    /**
     * Get shipping price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->_taxData->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                $this->getAddress()->getQuote()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * Retrieve icare center list.
     *
     * @return array
     */
    public function getIcareList()
    {
        if ($this->getCustomerId()) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $customer = $this->customerRepository->getById($this->getCustomerId());
            $website_id = $customer->getWebsiteId();
            $bind = ['website_id' => $website_id];
            $select = $connection->select()->from(
                self::CUSTOMER_TABLE,
                [self::CUSTOMER_ENTITY_FIELD, 'icare_center_type']
            );
            $select->where('website_id = :website_id');
            $select->where('icare_center_type is not null and icare_center_type > 0 and is_active = 1');
            $icareCenters = $connection->fetchAssoc($select, $bind);
            $data = [];
            if ($icareCenters) {
                foreach ($icareCenters as $icareCenter) {
                    $icare_center_type = $icareCenter['icare_center_type'];
                    $icare = $this->customerRepository->getById($icareCenter['entity_id']);
                    $addresses = $icare->getAddresses();
                    foreach ($addresses as $address) {
                        $data[$address->getId().'_'.$icare_center_type] = $address->getFirstname().' '.$address->getLastname();
                    }
                }
            }
            return $data;
        }
        return [];
    }

    /**
     * Return mix address id & address type
     *
     * @return false
     */
    public function getSelectedIcare()
    {
        return $this->icareAddressId.'_'.$this->icareAddressType;
    }

}
