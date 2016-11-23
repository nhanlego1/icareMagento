<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nhan
 * Date: 9/12/16
 * Time: 1:45 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Icare\Sales\Api;


interface SalesOrderInterface
{
    /**
     * List history by order
     *
     * @param int $orderId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function historyOrder($orderId);

    /**
     * List shipping by customer
     *
     * @param int $orderId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function shippingAddressCustomer($orderId);

    /**
     * List shipping by customer
     *
     * @param int $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function customerShippingAddress($customerId);

    /**
     * Add new shipping address and asign to order
     *
     * @param string $orderId
     * @param string $street
     * @param string $district
     * @param string $city
     * @param string $postcode
     * @param string $country
     * @param string $telephone
     * @param string $district
     * @return mixed
     */
    public function shippingAddressOrder($orderId, $street = null, $city = null, $postcode = null, $country = null, $telephone = null, $addressId = null, $district = null);
    /**
     * Add new shipping address and asign to order
     *
     * @param string $customerId
     * @param string $street
     * @param string $district
     * @param string $city
     * @param string $postcode
     * @param string $country
     * @param string $telephone
     * @param string $district
     * @return mixed
     */
    public function shippingAddressCustomerAdd($customerId, $street = null, $city = null, $postcode = null, $country = null, $telephone = null, $addressId = null, $district = null);

    /**
     * @Api
     * tracking preorder
     * @param int $userId
     * @param int $customerId
     * @param int $productId
     * @param string $reason
     * @return mixed
     */
    public function preorderTracking($userId, $customerId, $productId, $reason);
}