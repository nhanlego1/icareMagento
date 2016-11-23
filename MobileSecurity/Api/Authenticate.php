<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */

namespace Icare\MobileSecurity\Api;


use Icare\Exception\Model\IcareWebApiException;


class Authenticate extends MobileSecurityBaseApi implements AuthenticateInterface{
    
    /**
     * Authenticate
     * @param integer $customer_id
     * @param string $device_id
     * @param string $pincode
     * @return mixed
     */
    public function authenticate($customer_id, $pincode =FALSE,$device_id = null) {
        $iCareHelper = $this->om->get('Icare\Custom\Helper\ICareHelper');
        $logger = $this->om->get('\Psr\Log\LoggerInterface');
        try {
            $token = $iCareHelper->verifyCustomerPincode($customer_id, $pincode);
            $result = array();
            $result[] = ['token' => $token, 'code' => 200];
            return $result;
        } catch (\Exception $e) {
            $logger->error($e);
            throw new IcareWebApiException(500, __('Can not authentication'));
        }
    }
    
    public function verifyPincodeForConfirm($customer_id,$pincode,$device_id=null) {
        $iCareHelper = $this->om->get('Icare\Custom\Helper\ICareHelper');
        $logger = $this->om->get('\Psr\Log\LoggerInterface');
        try {
            $iCareHelper->verifyCustomerPincodeForConfirm($customer_id, $pincode);
            $result = array();
            $result[] = ['code' => 200, 'message' => __('Verify Pincode Successful')];
            return $result;
        } catch (\Exception $e) {
            $logger->error($e);
            throw new IcareWebApiException(500, __('Can not verify pincode'));
        }
    }
    /**
     * generate Token
     * @param integer $customer_id
     * @param string $device_id
     * @return mixed
     */

    public function generateToken($customer_id, $device_id = null)
    {
        // TODO: Implement generateToken() method.
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        //check customer first
        $result = [];
        if(empty($customer_id) || !is_numeric($customer_id)){
            $result = __('Customer Id is required.');
        }
        if(empty($device_id)){
            $result = __('Device Id is required.');
        }
        if($result){
            throw new IcareWebApiException(403,  $result);
        }
        $tokenObj = $om->create('Magento\Integration\Model\Oauth\Token');
        try{
            $tokenObj->createCustomerToken($customer_id);
        }catch(\Exception $ex){
            $result =__($ex->getMessage());
            throw new IcareWebApiException(403, $result);
        }

        $token = $tokenObj->loadByCustomerId($customer_id);
        $tokenData = $token->getData();
        if(isset($tokenData['token'])){
            try {
                $accessObj = $om->create('Icare\MobileSecurity\Model\AccessToken');
                $exist = $accessObj->loadByCustomerId($customer_id);
                if($exist->getId()){
                    $result = __('Device is ready exist access token.');
                    throw new IcareWebApiException(403, $result);
                }
                $accessObj->setCustomerId($customer_id);
                $accessObj->setDeviceId($device_id);
                $accessObj->setAccessToken($tokenData['token']);
                $accessObj->setIsLock(0);
                $accessObj->save();
            }catch(\Exception $e){
                $result = __($e->getMessage());
                throw new IcareWebApiException(403,  $result);
            }
            return $this->response([
                'access_token' => $tokenData['token'],
                'customer_id' => $customer_id,
                'device_id' => $device_id
            ]);
        }else{
            $result = __('Generate token fail.');
            throw new IcareWebApiException(403,  $result);
        }
    }

    /**
     * access Token
     * @param integer $customer_id
     * @param string $device_id
     * @return mixed
     */
    public function accessToken($customer_id, $device_id){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $accessObj = $om->create('Icare\MobileSecurity\Model\AccessToken');
        $accessToken = $accessObj->loadByCustomerId($customer_id);
        if($customer_id== $accessToken->getCustomerId() && $device_id==$accessToken->getDeviceId()){
            if($accessToken->getIsLock()==0){
                $status = true;
            }else{
                $status = false;
            }
            return $this->response([
                'code' => $status ? 200 : 403,
                'message' => $status ? __('Authentication successfully') : __('This device is locked. Please contact iCare support center to unlock device.'),
                'status' => $status
            ]);
        }else{
            return $this->response([
                'code' => 403,
                'message' => __('This device has not register to get access token.'),
                'status' => false
            ]);
        }
    }
}