<?php
namespace Icare\NetSuite\Observer;


use Icare\NetSuite\Helper\Payload;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Model\Order;

/**
 * Validate sales order data to make sure that there will be no invalid data for NetSuite
 * 
 * @author Nam Pham
 *        
 */
class OrderValidator implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $_customerFactory;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->_customerFactory = $customerFactory;
        
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer            
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $event = $observer->getEvent();
        $order = $event->getOrder();
        $oldOrder = $order->getData('unchangedOrder');
        if ($oldOrder !== null && $order->getStatus() != $oldOrder->getStatus()) {
            if ($order->getStatus() == Order::STATE_PROCESSING) {
                $this->validateOrder($order);
            }
        }
    }

    /**
     * validate order data
     * 
     * @param \Magento\Sales\Model\Order $order            
     */
    protected function validateOrder($order)
    {
        $customer = $order->getCustomer();
        
        // validate customer
        if (!$customer) $customer = $this->_customerFactory->create()->load($order->getCustomerId());
        $this->validateCustomer($customer);
        
        // validate addresses
        foreach ($order->getAddresses() as $address) {
            $this->validateAddress($address);
        }
        
        $error = null;
        
        // validate iCare address ID
        if (empty($order->getIcareAddressId())) {
            $error = __('iCare address is missing');
        }
        
        // validate line items
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) continue;
            $taxCode = Payload::getTaxRateFromOrderItem($item->getItemId(), $order->getResource()->getConnection());
            if (empty($taxCode)) {
                $error = __('Tax code is missing for item %2 (%1)', $item->getSku(), $item->getName());
                break;   
            }
        }
        
        if ($error) {
            throw new ValidatorException($error);
        }
    }
    
    /**
     * 
     * @param \Magento\Customer\Model\Address $address
     */
    protected function validateAddress($address) 
    {
        self::validateTelephone($address->getTelephone(), __('sales order\'s address'));
        
        $error = null;
        
        if (empty($address->getFirstname())) {
            $error = __('Firstname of address is missing');
        }
        
        if ($error) {
            throw new ValidatorException($error);
        }
    }
    
    /**
     * 
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function validateCustomer($customer)
    {   
        // validate telephone
        self::validateTelephone($customer->getTelephone(), __('customer'));
        
        $error = null;
        
        if (empty($customer->getOrganizationId())) {
            $error = __('Organization ID of customer is missing');
        }
        elseif (empty($customer->getFirstname())) {
            $error = __('Firstname of customer is missing');
        }
        
        if ($error) {
            throw new ValidatorException($error);   
        }
    }
    
    /**
     * 
     * @param string $telephone
     * @param mixed $entity
     * @throws ValidatorException
     */
    protected static function validateTelephone($telephone, $entity) 
    {
        if ($telephone && !empty($telephone) && !preg_match('/^\d{7,20}$/', $telephone)) {
            throw new ValidatorException(__('%2 telephone must has %1 or more digits.', 7, $entity));
        }
    }
    
}
