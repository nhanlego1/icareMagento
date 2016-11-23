<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nhan
 * Date: 9/12/16
 * Time: 1:49 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Icare\Sales\Model;

use Icare\Sales\Api\SalesOrderInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Custom\Helper\Custom;

class Order implements SalesOrderInterface
{

    /** @var \Psr\Log\LoggerInterface $_logger */
    private $_logger;

    /**
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Acl\CacheInterface $aclCache
    )
    {
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
        $this->_aclCache = $aclCache;

    }

    /**
     * Get list history by order
     * @param int $orderId
     * @return array
     */
    public function historyOrder($orderId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $objectOrder = $om->get('Magento\Sales\Model\Order');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['order_id' => $orderId];
        $select = $connection->select()->from(
            'sales_order_timeline',
            ['id', 'status', 'updated_date', 'order_id']
        );
        $select->where('order_id = :order_id');
        $select->order('id ASC');
        $histories = $connection->fetchAll($select, $bind);
        $data = [];
        if ($histories) {
            $is_delivery = false;
            foreach ($histories as $history) {
                if ($history['status'] == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_DELIVERED) {
                    $is_delivery = true;
                }
            }
            foreach ($histories as $history) {
                if ($history['status'] == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_DELIVERY_FAILED && $is_delivery == true) {
                    continue;
                }
                $item = array(
                    'id' => $history['id'],
                    'order_id' => $history['order_id'],
                    'status' => $history['status'],
                    'date' => strtotime($history['updated_date']),
                );
                if ($is_delivery == false) {
                    if ($history['status'] == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_DELIVERY_FAILED) {
                        $order = $objectOrder->load($history['order_id']);
                        $shipmentCollection = $order->getShipmentsCollection();
                        $reasons = array();
                        foreach ($shipmentCollection as $shipment) {
                            $reasons = $this->getDeliveryFailedReason($shipment->getId());
                        }
                        $item['failed_reason'] = $reasons;
                    }
                }
                
                $data[] = $item;
            }
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Create shipping address and asign to order
     * @param $orderId
     * @param $street
     * @param $district
     * @param $city
     * @param $postcode
     * @param $country
     * @param $telephone
     * @return mixed|void
     */
    public function shippingAddressOrder($orderId, $street = null, $city = null, $postcode = null, $country = null, $telephone = null, $addressId = null,  $district = null)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
        $customer = $om->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
        $result = array();
        if (empty($orderId) || !is_numeric($orderId)) {
            $result[] = new IcareException(__('Order Id is required.'));
        }

        if (!$order->getId()) {
            $result[] = new IcareException(__('Order is not exist.'));
        }
        if ($result) {
            $this->_logger->error(print_r($result));
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }

        if (!empty($addressId)) {
            $address = $om->create('Magento\Customer\Model\Address')->load($addressId);
        } else {

            if (empty($street)) {
                $result[] = new IcareException(__('Street is required.'));
            }

            if (empty($city)) {
                $result[] = new IcareException(__('City is required.'));
            }
            if (empty($postcode)) {
                $result[] = new IcareException(__('Postcode is required.'));
            }
            if (empty($country)) {
                $result[] = new IcareException(__('Country is required.'));
            }
            if ($result) {
                $this->_logger->error(print_r($result));
                throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
            }


            //set shipping address for customer
            $address = $om->create('Magento\Customer\Model\Address');
            $address->setCustomerId($order->getCustomerId());
            $address->setCountryId(strtoupper($country));
            $address->setStreet(array($street));
            $address->setPostcode($postcode);
            if (!empty($telephone)) {
                $address->setTelephone($telephone);
            }
            $address->setCity($city);
            $address->setFirstname($order->getCustomerFirstname());
            $address->setLastname($order->getCustomerLastname());
            if ($district) {

                if (Custom::create()->checkExistRegion(strtoupper($country), $district)) {
                    $region_id = $this->checkExistRegion(strtoupper($country), $district);
                } else {
                    $recode = explode(' ', $district);
                    $code = '';
                    foreach ($recode as $co) {
                        $code .= substr($co, 1);
                    }
                    $code = strtoupper($code);
                    $region = $om->create('Magento\Directory\Model\Region');
                    $region->setCountryId(strtoupper($country));
                    $region->setCode($code);
                    $region->setDefaultName($district);
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
        }
        //asigned shipping address to order
        $street = $address->getstreet();
        $order_address = $om->create('Magento\Sales\Model\Order\Address');
        $order_address->setParentId($order->getId());
        $order_address->setCustomerAddressId($address->getEntityId());
        $order_address->setStreet(reset($street));
        $order_address->setCustomerId($order->getCustomerId());
        $order_address->setPostcode($address->getPostcode());
        $order_address->setCity($address->getCity());
        $order_address->setEmail($customer->getEmail());
        $order_address->setFirstname($order->getCustomerFirstname());
        $order_address->setLastname($order->getCustomerLastname());
        $order_address->setCountryId($address->getCountryId());
        $order_address->setTelephone($address->getTelephone());
        $order_address->setAddressType('shipping');
        //clear shiiping address before add new
        $shipping = $order->getShippingAddress();
        if ($shipping) {
            $old_address = $om->create('Magento\Sales\Model\Order\Address')->load($shipping->getEntityId());
            $old_address->delete();
        }

        //assign address to order
        try {
            $order_address->save();
            $this->_aclCache->clean();
            $data[] = $order_address->getData();
            return $data;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    /**
     * Create shipping address and asign to order
     * @param $customerId
     * @param $street
     * @param $district
     * @param $city
     * @param $postcode
     * @param $country
     * @param $telephone
     * @return mixed|void
     */
    public function shippingAddressCustomerAdd($customerId, $street = null, $city = null, $postcode = null, $country = null, $telephone = null, $addressId = null, $district = null)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $om->create('Magento\Customer\Model\Customer')->load($customerId);
        $result = array();
        if (empty($customerId) || !is_numeric($customerId)) {
            $result[] = new IcareException(__('Customer Id is required.'));
        }

        if (!$customer->getId()) {
            $result[] = new IcareException(__('Customer is not exist.'));
        }
        if ($result) {
            $this->_logger->error(print_r($result));
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }

        if (!empty($addressId)) {
            $address = $om->create('Magento\Customer\Model\Address')->load($addressId);
        } else {

            if (empty($street)) {
                $result[] = new IcareException(__('Street is required.'));
            }

            if (empty($city)) {
                $result[] = new IcareException(__('City is required.'));
            }
            if (empty($postcode)) {
                $result[] = new IcareException(__('Postcode is required.'));
            }
            if (empty($country)) {
                $result[] = new IcareException(__('Country is required.'));
            }
            if ($result) {
                $this->_logger->error(print_r($result));
                throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
            }

            //set shipping address for customer
            $address = $om->create('Magento\Customer\Model\Address');
            $address->setCustomerId($customerId);
            $address->setCountryId(strtoupper($country));
            $address->setStreet(array($street));
            $address->setPostcode($postcode);
            if (!empty($telephone)) {
                $address->setTelephone($telephone);
            }
            $address->setCity($city);
            $address->setFirstname($customer->getFirstname());
            $address->setLastname($customer->getLastname());
            if ($district) {

                if (Custom::create()->checkExistRegion(strtoupper($country), $district)) {
                    $region_id = $this->checkExistRegion(strtoupper($country), $district);
                } else {
                    $recode = explode(' ', $district);
                    $code = '';
                    foreach ($recode as $co) {
                        $code .= substr($co, 1);
                    }
                    $code = strtoupper($code);
                    $region = $om->create('Magento\Directory\Model\Region');
                    $region->setCountryId(strtoupper($country));
                    $region->setCode($code);
                    $region->setDefaultName($district);
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
        }

        //assign address to order
        try {
            $data[] = $address->getData();
            return $data;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    /**
     * List shipping by customer
     *
     * @param int $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    public function shippingAddressCustomer($orderId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
        $customer = $om->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
        $result = [];
        if (empty($orderId) || !is_numeric($orderId)) {
            $result[] = new IcareException(__('Order id is required.'));
        }
        if (!$order->getId()) {
            $result[] = new IcareException(__('Order is not exist.'));
        }

        if ($result) {
            $this->_logger->error(print_r($result));
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        $addresses = $customer->getAddresses();
        $data = [];
        foreach ($addresses as $address) {
            $street = $address->getStreet();
            $region = $om->create('Magento\Directory\Model\Region')->load($address->getRegionId());
            $street = reset($street);
            $item = array(
                'addressId' => $address->getEntityId(),
                'address' => $street . ' | ' . $address->getCity(). ' | '.$region->getDefaultName() . ' | ' . $address->getPostcode() . ' | ' . $address->getCountryId() . ' | ' . $address->getTelephone()
            );
            $data[] = $item;
        }
        return $data;
    }

    public function customerShippingAddress($customerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $om->create('Magento\Customer\Model\Customer')->load($customerId);
        $result = [];
        if (empty($customerId) || !is_numeric($customerId)) {
            $result[] = new IcareException(__('Customer id is required.'));
        }
        if (!$customer->getId()) {
            $result[] = new IcareException(__('Customer is not exist.'));
        }

        if ($result) {
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        $addresses = $customer->getAddresses();
        $data = [];
        foreach ($addresses as $address) {
            $street = $address->getStreet();
            $region = $om->create('Magento\Directory\Model\Region')->load($address->getRegionId());
            $street = reset($street);
            $item = array(
                'addressId' => $address->getEntityId(),
                'address' => $street . ' | ' . $address->getCity(). ' | '.$region->getDefaultName() . ' | ' . $address->getPostcode() . ' | ' . $address->getCountryId() . ' | ' . $address->getTelephone()
            );
            $data[] = $item;
        }
        return $data;
    }

    protected function getDeliveryFailedReason($shipmentId) {
        $resource  = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipmentId)
            ->where('delivery_failed_reason is not null');
        $rows = $connection->fetchAssoc($select);
        $reasons = array();
        foreach($rows as $row) {
            $reasons[] = $row['reason_detail'];
        }

        return $reasons;
    }

    /**
     * @param int $userId
     * @param int $customerId
     * @param int $productId
     * @param string $reason
     */
    public function preorderTracking($userId, $customerId, $productId, $reason){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $result = [];

        if (empty($userId) || !is_numeric($userId)) {
            $result[] = new IcareException(__('User id is required.'));
        }

        if (empty($customerId) || !is_numeric($customerId)) {
            $result[] = new IcareException(__('Customer id is required.'));
        }

        if (empty($productId) || !is_numeric($productId)) {
            $result[] = new IcareException(__('Product id is required.'));
        }

        if ($result) {
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }

        $resource  = $om->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $insertValues = [
            'user_id' => $userId,
            'customer_id' => $customerId,
            'product_id' => $productId,
            'reason' =>$reason,
        ];
        try {
            $connection->insert('icare_preorder_tracking',
                $insertValues);
            $status = [];
            $status['code'] = 200;
            $status['message'] = __('Save success.');
            return [$status];
        }catch (\Exception $ex){
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }

    }

}