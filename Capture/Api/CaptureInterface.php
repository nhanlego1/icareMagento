<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/2/2016
 * Time: 5:30 PM
 */
namespace Icare\Capture\Api;

interface CaptureInterface {
    /**
     * add customer info
     * @param string $customerId
     * @param string $deviceId
     * @param string $deviceName
     * @param string $osVesion
     * @param string $appVersion
     * @param string $lat
     * @param string $long
     * @param string $orderId
     * @param string $isIcareMemberConfirm 
     * @return mixed
     */
    public function addCustomer($customerId, $deviceId, $deviceName, $osVersion, $appVersion, $lat, $long, $orderId, $isIcareMemberConfirm = 1);

    /**
     * Get infor customer
     * @param string $customerId
     * @return mixed
     */
    public function customerInfo($customerId);

    /**
     * Get infor order
     * @param string $orderId
     * @return mixed
     */
    public function orderInfo($orderId);
}