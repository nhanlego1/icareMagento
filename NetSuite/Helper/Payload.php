<?php

namespace Icare\NetSuite\Helper;

use Icare\Catalog\Model\Product;
use Icare\Sales\Api\ShipmentInterface;

/**
 * Payload helper contains static functions to generate payload for entities which is posted to NetSuite
 * @author Nam Pham
 *
 */
class Payload
{
    /**
     *
     * @param  \Magento\Customer\Model\Customer $customer
     * @return array
     */
    public static function convertCustomerPayload($customer)
    {
        $payload = array(
            'entity_id' => $customer->getEntityId(),
            'company_id' => $customer->getData('organization_id'),
            'gender' => $customer->getData('gender'),
            'firstname' => $customer->getFirstname(),
            'middlename' => $customer->getMiddlename(),
            'lastname' => $customer->getLastname(),
            'phone' => $customer->getTelephone(),
            'email' => $customer->getEmail(),
        );

        $addresses = $customer->getAddresses();
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $type = $address->getData('address_type');
                $payload[($type == 'billing'?$type:'shipping').'_address'] = self::convertAddressPayload($address);
            }
        }
        // ensure shipping_address
        if (empty($payload['shipping_address']) && $customer->getDefaultShippingAddress()) {
            $payload['shipping_address'] = Payload::convertAddressPayload($customer->getDefaultShippingAddress());
        }

        // ensure billing_address
        if (empty($payload['billing_address']) && $customer->getDefaultBillingAddress()) {
            $payload['billing_address'] = Payload::convertAddressPayload($customer->getDefaultBillingAddress());
        }

        return $payload;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public static function convertAddressPayload($address)
    {
        $payload = array(
            'entity_id' => $address->getEntityId(),
            'firstname' => $address->getFirstname(),
            'middlename' => $address->getMiddlename(),
            'lastname' => $address->getLastname(),
            'telephone' => $address->getTelephone(),
            'email' => $address->getEmail(),
            'street' => implode(', ', $address->getStreet()),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId(),
        );

        return $payload;
    }

    /**
     * get item SKU in NetSuite
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public static function getNetSuiteItemSku($item)
    {
        $productOptions = $item->getProductOptions();
        $optionTypeSku = null;
        if ($productOptions) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            if (isset($productOptions['info_buyRequest']['options'])) {
                $productCombine1 = implode('|', array_values($productOptions['info_buyRequest']['options']));
                $productCombine2 = implode('|', array_reverse(array_values($productOptions['info_buyRequest']['options'])));
                $select  = $connection->select()
                    ->from(Product::NETSUITE_CATALOG_PRODUCT_OPTION)
                    ->where('product_id = ?', $item->getProductId())
                    ->where('option_type_id_array in (?)', [$productCombine1, $productCombine2]);
                $row = $connection->fetchRow($select);
                if ($row) {
                    $optionTypeSku = $row['option_type_sku'];
                }
            } elseif (isset($productOptions['options'])) {
                $productCombines = array();
                foreach ($productOptions['options'] as $option) {
                    $productCombines[] = $option['option_value'];
                }
                $productCombine1 = implode('|', $productCombines);
                $productCombine2 = implode('|', array_reverse($productCombines));
                $select  = $connection->select()
                    ->from(Product::NETSUITE_CATALOG_PRODUCT_OPTION)
                    ->where('product_id = ?', $item->getProductId())
                    ->where('option_type_id_array in (?)', [$productCombine1, $productCombine2]);
                $row = $connection->fetchRow($select);
                if ($row) {
                    $optionTypeSku = $row['option_type_sku'];
                }
            }
        }
        return $optionTypeSku?$optionTypeSku:$item->getSku();
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return array
     */
    public static function convertItemPayload($item)
    {
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $payload = array(
            'entity_id' => $item->getItemId(),
            'sku' => self::getNetSuiteItemSku($item),
            'name' => $item->getName(),
            'qty_ordered' => strval(intval($item->getQtyOrdered())),
            'rate' => strval($item->getPrice()),
            'amount' => strval($item->getRowTotal()),
            'amount_incl_tax' => strval($item->getRowTotalInclTax()),
            'discount_percent' => strval($item->getDiscountPercent()),
            'discount_amount' => strval($item->getDiscountAmount()),
            'tax_amount' => $item->getTaxAmount(),
            'tax_code' => self::getTaxRateFromOrderItem($item->getId(), $connection)
        );

        return $payload;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param boolean $includeAddresses
     * @param boolean $includeItems
     * @return array
     */
    public static function convertOrderPayload($order, $includeAddresses = true, $includeItems = true)
    {
        $payload = array(
            '_message_type' => 'sales_order',
            'entity_id' => $order->getIncrementId(),
            'status' => $order->getStatus(),
            'customer_note' => $order->getIncrementId(),
            'store_currency_code' => $order->getStoreCurrencyCode(),
            'subtotal' => $order->getSubtotal(),
            'shipping_amount' => $order->getShippingAmount(),
            'tax_amount' => $order->getTaxAmount(),
            'created_at' => strtotime($order->getCreatedAt()),
            'updated_at' => strtotime($order->getUpdatedAt()),
        );

        if ($includeAddresses) {
            // read iCare Address
            $iCareAddr = $order->getIcareAddressId();
            if ($iCareAddr) {
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                /**
                 * 
                 * @var \Magento\Customer\Model\Address $addr
                 */
                $addr = $om->create('Magento\Customer\Model\Address');
                $addr->load($iCareAddr);
            }
            else {
                $addr = null;
            }
            $payload['location_id'] = $addr == null || empty($addr->getLocationId())?null:$addr->getLocationId();
            
            // read addresses
            $addresses = $order->getData('addresses');
            if ($addresses == null) {
                $addresses = $order->getAddresses();
            }
            if (!empty($addresses)) {
                foreach ($addresses as $address) {
                    $type = $address->getData('address_type');
                    $payload[($type == 'billing'?$type:'shipping').'_address'] = self::convertAddressPayload($address);
                }
            }
    
            // ensure shipping address is serialized (if any)
            if (empty($payload['shipping_address']) && !empty($order->getData('shipping_address_id'))) {
                $shipping = $order->getAddressById($order->getData('shipping_address_id'));
                $payload['shipping_address'] = self::convertAddressPayload($shipping);
            }
    
            // ensure billing address is serialized (if any)
            if (empty($payload['billing_address']) && !empty($order->getData('billing_address_id'))) {
                $billing = $order->getAddressById($order->getData('billing_address_id'));
                $payload['billing_address'] = self::convertAddressPayload($billing);
            }
        }

        if ($includeItems && !empty($order->getItems())) {
            foreach ($order->getItems() as $item) {
                if ($item->getProductType() !== \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) {
                    $payload['items'][] = self::convertItemPayload($item);
                }
            }
        }

        return $payload;
    }


    public static function getTaxRateFromOrderItem($orderItemId, $connection)
    {
        $select = $connection->select()->from('sales_order_item', ['tax_rate_code'])
            ->where('item_id = ?', $orderItemId);
        $row = $connection->fetchAssoc($select);
        $row = reset($row);
        return $row['tax_rate_code'];
    }
    
    /**
     * convert shipment data to Item Fulfillment in NetSuite
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     */
    public static function convertShipmentPayload($shipment)
    {
        // searialize status
        switch ($shipment->getShipmentStatus()) {
            case ShipmentInterface::STATUS_DELIVERED:
                $status = 'delivered';
                break;
            case ShipmentInterface::STATUS_FAILED:
                $status = 'failed';
                break;
            default:
                $status = 'shipping';
        }
        
        // serialize comments
        $commentArray = $shipment->getComments();
        $comments = array();
        if ($commentArray) {
            foreach ($commentArray as $comment) {
                $comments[] = array(
                  'comment' => $comment->getComment(),
                  'created_at' => strtotime($comment->getCreatedAt())
                );
            }
        }
        return array(
            '_message_type' => 'item_fulfillment',
            'id' => $shipment->getId(),
            'status' => $status,
            'comments' => $comments,
            'fulfillment_id' => $shipment->getFulfillmentId(),
        );
    }
}
