<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Model;
use Zend\Form\Element\DateTime;

class MobileSecurity extends \Magento\Framework\Model\AbstractModel implements MobileSecurityInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'icare_mobile_security';
    const VAR_HASH_SALT_KEY = 'icare_member_passcode';
    const DEFAULT_MEMBER_HASH_SALT = 'ldz0iO1wx';
    protected function _construct()
    {
        $this->_init('Icare\MobileSecurity\Model\ResourceModel\MobileSecurity');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get customer ID
     * getCustomerId
     * @return integer
     */
    public function getCustomerId() {
        return $this->getData('customer_id');
    }

    /**
     * setCustomerId
     * @param integer $customer_id
     * @return mixed
     */
    public function setCustomerId($customer_id) {
        // TODO: Implement setCustomerId() method.
        return $this->setData('customer_id',$customer_id);
    }

    /**
     * getDeviceId
     * @return string
     */
    public function getDeviceId() {
        // TODO: Implement getDeviceId() method.
        return $this->getData('device_id');
    }

    /**
     * setDeviceId
     * @param string $device_id
     * @return mixed
     */
    public function setDeviceId($device_id) {
        // TODO: Implement setDeviceId() method.
        return $this->setData('device_id',$device_id);
    }

    /**
     * getPinCode
     * @return string
     */
    public function getPinCode() {
        // TODO: Implement getPinCode() method.
        return $this->getData('pincode');
    }

    /**
     * setPinCode
     * @param string $pincode
     * @return mixed
     */
    public function setPinCode($pincode) {
        // TODO: Implement setPinCode() method.
        return $this->setData('pincode',$pincode);
    }

    /**
     * getStatus
     * @return integer
     */
    public function getStatus() {
        // TODO: Implement getStatus() method.
        return $this->getData('status');
    }

    /**
     * setStatus
     * @param integer $status
     * @return mixed
     */
    public function setStatus($status) {
        // TODO: Implement setStatus() method.
        return $this->setData('status',$status);
    }

    /**
     * getDeviceInfo
     * @return mixed
     */
    public function getDeviceInfo() {
        // TODO: Implement getDeviceInfo() method.
        return $this->getData('device_info');
    }

    /**
     * setDeviceInfo
     * @param string $info
     * @return mixed
     */
    public function setDeviceInfo($info) {
        // TODO: Implement setDeviceInfo() method.
        return $this->setData('device_info',$info);
    }

    /**
     * getLastLogin
     * @return mixed
     */
    public function getLastLogin() {
        // TODO: Implement getLastLogin() method.
        return $this->getData('last_login');
    }

    /**
     * setLastLogin
     * @param \DateTime $date
     * @return mixed
     */
    public function setLastLogin($date) {
        // TODO: Implement setLastLogin() method.
        $this->setData('last_login',$date->format('Y-m-d H:i:s'));
    }

    /**
     * getUpdatedAt
     * @return mixed
     */
    public function getUpdatedAt() {
        // TODO: Implement getUpdatedAt() method.
        return $this->getData('updated_at');
    }

    /**
     * setUpdatedAt
     * @param \DateTime $date
     * @return mixed
     */
    public function setUpdatedAt($date) {
        // TODO: Implement setUpdatedAt() method.
        return $this->setData('updated_at',$date->format('Y-m-d H:i:s'));
    }

    /**
     * getCreatedAt
     * @return mixed
     */
    public function getCreatedAt() {
        // TODO: Implement getCreatedAt() method.
        return $this->getData('created_at');
    }

    public function loadByConditions($param = []){

    }

    /**
     * beforeSave
     * @return $this
     */
    public function beforeSave() {
        if($this->getCreatedAt() == NULL)
            $this->setData('created_at',new \Datetime('now'));
        if($this->getUpdatedAt() == NULL)
            $this->setData('updated_at',new \Datetime('now'));
        return parent::beforeSave(); // TODO: Change the autogenerated stub
    }

    /**
     * className
     * @return string
     */
    public static function className(){
        return get_called_class();
    }
}
