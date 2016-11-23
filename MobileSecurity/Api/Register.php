<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:20
 */
namespace Icare\MobileSecurity\Api;

use Icare\Exception\Model\IcareWebApiException;
use Icare\MobileSecurity\Helper\ApiHelper;
use Icare\MobileSecurity\Model\MobileSecurity;
use Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection;

class Register extends MobileSecurityBaseApi implements RegisterInterface
{

    private $collection;

    /**
     * Register new device and pincode
     * 
     * @param integer $customer_id                 
     * @param string $pincode            
     * @param string $device_id
     * @return mixed
     */
    public function register($customer_id, $pincode,$device_id = null)
    {
        $iCareHelper = $this->om->get('Icare\Custom\Helper\ICareHelper');
        $logger = $this->om->get('\Psr\Log\LoggerInterface');
        try {
            $iCareHelper->registerPincode($customer_id, $pincode);
            $token = $iCareHelper->verifyCustomerPincode($customer_id, $pincode);
            $result = array();
            $result[] = ['token' => $token];
            return $result;
        } catch (\Exception $e) {
            $logger->error($e);
            throw new IcareWebApiException(500, __('Can not register pincode'));
        }
    }

    /**
     * Update current pincode
     * 
     * @param integer $customer_id            
     * @param string $device_id            
     * @param string $old_pincode            
     * @param string $new_pincode            
     * @return mixed
     */
    public function update($customer_id, $new_pincode, $old_pincode = false,$device_id=null)
    {
        $iCareHelper = $this->om->get('Icare\Custom\Helper\ICareHelper');
        $logger = $this->om->get('\Psr\Log\LoggerInterface');
        try {
            $iCareHelper->registerPincode($customer_id, $new_pincode);
            $result = array();
            $result[] = ['code' => 200, 'message' => __('Update Pincode Successful')];
            return $result;
        } catch (\Exception $e) {
            $logger->error($e);
            throw new IcareWebApiException(500, __('Can not update pincode'));
        }
    }

    /**
     * Reset current pincode
     * 
     * @param integer $customer_id                 
     * @return mixed
     */
    public function reset($customer_id)
    {
        $iCareHelper = $this->om->get('Icare\Custom\Helper\ICareHelper');
        $logger = $this->om->get('\Psr\Log\LoggerInterface');
        try {
            $iCareHelper->resetPincode($customer_id);
            $result = array();
            $result[] = ['code' => 200, 'message' => __('Reset Pincode Successful')];
            return $result;
        } catch (\Exception $e) {
            $logger->error($e);
            throw new IcareWebApiException(500, __('Can not reset pincode'));
        }
    }
}
