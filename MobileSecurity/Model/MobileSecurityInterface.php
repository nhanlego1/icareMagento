<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Model;


interface MobileSecurityInterface
{
    /**
     * Get record ID
     * getId
     * @return integer
     */
    public function getId();

    /**
     * Get customer ID
     * getCustomerId
     * @return integer
     */
    public function getCustomerId();

    /**
     * setCustomerId
     * @param integer $customer_id
     * @return mixed
     */
    public function setCustomerId($customer_id);

    /**
     * getDeviceId
     * @return string
     */
    public function getDeviceId();

    /**
     * setDeviceId
     * @param string $device_id
     * @return mixed
     */
    public function setDeviceId($device_id);

    /**
     * getPinCode
     * @return string
     */
    public function getPinCode();

    /**
     * setPinCode
     * @param string $pincode
     * @return mixed
     */
    public function setPinCode($pincode);

    /**
     * getStatus
     * @return integer
     */
    public function getStatus();

    /**
     * setStatus
     * @param integer $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * getDeviceInfo
     * @return mixed
     */
    public function getDeviceInfo();

    /**
     * setDeviceInfo
     * @param string $info
     * @return mixed
     */
    public function setDeviceInfo($info);

    /**
     * getLastLogin
     * @return mixed
     */
    public function getLastLogin();

    /**
     * setLastLogin
     * @param \DateTime $date
     * @return mixed
     */
    public function setLastLogin($date);

    /**
     * getUpdatedAt
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * setUpdatedAt
     * @param \DateTime $date
     * @return mixed
     */
    public function setUpdatedAt($date);

    /**
     * getCreatedAt
     * @return mixed
     */
    public function getCreatedAt();
}