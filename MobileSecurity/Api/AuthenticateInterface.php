<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:43
 */

namespace Icare\MobileSecurity\Api;


interface AuthenticateInterface {

    /**
     * Authenticate
     * @param integer $customer_id
     * @param string $device_id
     * @param string $pincode
     * @return mixed
     */
    public function authenticate($customer_id,$pincode,$device_id=null);

    /**
     * Generate Token
     * @param integer $customer_id
     * @param string $device_id
     * @return mixed
     */
    public function generateToken($customer_id, $device_id=null);

    /**
     * access Token
     * @param integer $customer_id
     * @param string $device_id
     * @return mixed
     */
    public function accessToken($customer_id, $device_id);
    
    
    /**
     * Authenticate
     * @param integer $customer_id
     * @param string $device_id
     * @param string $pincode
     * @return mixed
     */
    public function verifyPincodeForConfirm($customer_id,$pincode,$device_id=null);
}