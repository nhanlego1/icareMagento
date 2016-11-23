<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 15:18
 */
namespace Icare\MobileSecurity\Helper;

use Icare\MobileSecurity\Model\MobileSecurity;
use Magento\Framework\App\ObjectManager;

class ApiHelper extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * locked : this customer is locked by administrators
     */
    const STATUS_LOCKED = 'locked';
    /**
     * active :  this is normal customer status
     */
    const STATUS_ACTIVE = 'active';
    /**
     * changed : This customer has been created a pincode with another device
     * and changing to use the new one
     */
    const STATUS_CHANGED = 'changed';

    /**
     * This status made by a reset pincode ticket
     */
    const STATUS_RESET = 'reset';

    /**
     * This customer is a new one. No security record created before
     */

    const STATUS_UNREGISTER = 'unregister';

    /**
     * wakeup
     * 
     * @return \Icare\MobileSecurity\Helper\ApiHelper
     */
    public static function getInstance()
    {
        return ObjectManager::getInstance()->get(self::className());
    }

    /**
     * className
     * 
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * getAppData
     * 
     * @param
     *            $customer_id
     * @param
     *            $device_id
     * @return array
     */
    public function getAppData($customer_id, $device_id = null)
    {

        $data = [
            'status' => self::STATUS_ACTIVE,//locked,unregister,reset,changed
            'device_id' => NULL
        ];
        /**@var \Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection $collection**/
        $collection = ObjectManager::getInstance()->get('Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection');
        $collection->addFieldToFilter('customer_id', $customer_id);
        //It will be use in the future
       /* if(!empty($device_id))
            $collection->addFieldToFilter('device_id',$device_id);*/
        $models = $collection->load()->getItems();
        if (count($models) == 0) {
            $data['status'] = self::STATUS_UNREGISTER;
            $data['device_id'] = $device_id;
        } else {
            /**@var MobileSecurity $model**/
            $registered = FALSE;
            foreach ($models as $model) {
                if ($model->getStatus() == 0)
                    $data['status'] = self::STATUS_LOCKED;
                else{
                    if($model->getPinCode() == null){
                        $data['status'] = self::STATUS_RESET;
                    }
                    else{
                        $data['status'] = self::STATUS_ACTIVE;
                    }

                }
                $data['device_id'] = $device_id;
                $registered = TRUE;
                break;
            }
            /*if (! $registered) {
                $data['status'] = self::STATUS_CHANGED;
            }*/
        }
        $data['access_token'] = $this->getCustomerToken($customer_id,$device_id);
        return $data;
    }

    private function getCustomerToken($customer_id,$device_id){
        if(empty($device_id)) return NULL;
        $om = ObjectManager::getInstance();
        $tokenObj = $om->create('Magento\Integration\Model\Oauth\Token');
        try{
            $tokenObj->createCustomerToken($customer_id);
            $token = $tokenObj->loadByCustomerId($customer_id);
            $tokenData = $token->getData();
            if(isset($tokenData['token'])){
                try {
                    /**@var \Icare\MobileSecurity\Model\AccessToken $accessObj**/
                    $accessObj = $om->create('Icare\MobileSecurity\Model\AccessToken');
                    /**@var \Icare\MobileSecurity\Model\AccessToken $exist**/
                    $exist = $accessObj->loadByCustomerId($customer_id);
                    if($exist->getId()){
                        return $exist->getAccessToken();
                    }
                    $accessObj->setCustomerId($customer_id);
                    $accessObj->setDeviceId($device_id);
                    $accessObj->setAccessToken($tokenData['token']);
                    $accessObj->setIsLock(0);
                    $accessObj->save();
                    return $accessObj->getAccessToken();
                }catch(\Exception $e){
                    //Log error
                    $this->_logger->log($e);
                }

            }
        }catch(\Exception $ex){
            $this->_logger->log($e);
        }
        return NULL;
    }

    /**
     * verifyPasscode
     * 
     * @param
     *            $customer_id
     * @param
     *            $device_id
     * @return bool
     */
    public function verifyPasscode($customer_id, $device_id = NULL)
    {
        /**
         *
         * @var \Icare\MobileSecurity\Api\MobileSecurityBaseApi $auth
         */
        $auth = ObjectManager::getInstance()->get('Icare\MobileSecurity\Api\MobileSecurityBaseApi');
        return $auth->authorize($customer_id, false, $device_id);
    }


    /**
     * resetPincode
     * @param $customer_id
     * @return bool
     */
    public function resetPincode($customer_id){
        /**@var MobileSecurity $model */
        $collection = ObjectManager::getInstance()->get('Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection');
        $models = $collection->addFieldToFilter('customer_id',$customer_id)
                             ->load()->getItems();
        if(count($models)>0){
            foreach($models as $model){
                $model->setPinCode(null);
                $model->save();
            }
            return true;
        }
        return FALSE;
    }

    /**
     * resetPincode
     * @param $customer_id
     * @return bool
     */
    public function lockDevice($customer_id){
        /**@var MobileSecurity $model */
        $access = ObjectManager::getInstance()->create('Icare\MobileSecurity\Model\AccessToken');
        $accessToken = $access->loadByCustomerId($customer_id);
        try{
            if($accessToken->getId() > 0){
                $accessToken->setIsLock(1);
                $accessToken->save();
                return true;
            }
            return false;
        }catch(\Exception $ex){
            return FALSE;
        }

    }
}