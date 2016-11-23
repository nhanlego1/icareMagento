<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/11/16
 * Time: 5:02 PM
 */
namespace Icare\IcareOrderApi\Api;

use Icare\IcareOrderApi\Api\Data\GetOrderListInfoInterface;
use Icare\IcareOrderApi\Api\Data\GetOrderDetailInfoInterface;
use Icare\IcareOrderApi\Api\Data\GetOrderListInfoByCustomerInterface;
use Icare\IcareOrderApi\Api\Data\OptionValueInterface;
use Icare\IcareOrderApi\Api\Data\AddressValueInterface;

interface IcareOrderInterface {
    /**
     * Delete order
     *
     * @param int $orderId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function confirmOrder($orderId);


    /**
     * Confirm order with passcode
     *
     * @param int $orderId
     * @param string $device_id
     * @param string $note
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function confirmOrderWithPasscode($orderId,$device_id, $note = 'no comment');

    /**
     * Description
     * @param GetOrderListInfoByCustomerInterface $getOrderInfo 
     * @return mixed
     */
    public function getCustomerOrderList(GetOrderListInfoByCustomerInterface $getOrderInfo);

    /**
     * @param GetOrderListInfoInterface $getOrderInfo
     * @return mixed
     */
    public function getOrderListV2(GetOrderListInfoInterface $getOrderInfo);

    /**
     * @param string $userId
     * @return mixed
     */
    public function getOrderListByUserId($userId);
    /**
     * @param string $userId
     * @param string $from
     * @param string $to
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByUserIdFromTo($userId, $from = null, $to = null, $pageNum = null, $pageSize = null);

    /**
     * @param string $customerId
     * @param string $from
     * @param string $to
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByCustomerIdFromTo($customerId, $from, $to, $pageNum, $pageSize);

    /**
     * @param string $customerId
     * @param string $pageNum
     * @param string $pageSize
     * @return mixed
     */
    public function getOrderListByCustomerIdV4($customerId, $pageNum = null, $pageSize = null);

    /**
     * @param string $orderNo
     * @param string $action
     * @param string $note
     * @return mixed
     */
    public function actionOrder($orderNo, $action,  $note = 'no comment');

    /**
     * @param string $orderIncrementId
     * @return mixed
     */
    public function getOrderDetailV3($orderIncrementId);

    /**
     * Create order
     *
     * @param int $customerID
     * @param  int $itemID
     * @param int $qty
     * @param int $userId
     * @param int $itemInstallment
     * @param Icare\IcareOrderApi\Api\Data\OptionValueInterface[] $optionValues
     * @param string $savingAccount
     * @param string $savingAmount
     * @param int $addressIcareId
     * @param int $customerAddressId
     * @param Icare\IcareOrderApi\Api\Data\AddressValueInterface[] $addressValue
     * @param string $shippingMethod
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function placeOrderV6($customerID, $itemID, $qty, $userId, $itemInstallment, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $addressIcareId = null, $customerAddressId = null, $addressValue = null, $shippingMethod = null);


    /**
     * calculate order information
     *
     * @param int $customerID
     * @param  int $itemID
     * @param int $qty
     * @param int $userId
     * @param int $itemInstallment
     * @param Icare\IcareOrderApi\Api\Data\OptionValueInterface[] $optionValues
     * @param string $savingAccount
     * @param string $savingAmount
     * @param int $addressIcareId
     * @param int $customerAddressId
     * @param Icare\IcareOrderApi\Api\Data\AddressValueInterface[] $addressValue
     * @param string $shippingMethod
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function calculateOrderInfo($customerID, $itemID, $qty, $userId, $itemInstallment, $optionValues = null, $savingAccount = 0, $savingAmount = 0, $addressIcareId = null, $customerAddressId = null, $addressValue = null, $shippingMethod = null);

    /**
     * @param GetOrderListInfoInterface $getOrderInfo
     * @return mixed
     */
    public function getOrderList(GetOrderListInfoInterface $getOrderInfo);
}