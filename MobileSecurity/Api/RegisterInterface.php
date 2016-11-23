<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:43
 */

namespace Icare\MobileSecurity\Api;


interface RegisterInterface {

    /**
     * Register new device and pincode
     * @param integer $customer_id
     * @param string $device_id
     * @param string $pincode
     * @return mixed
     */
    public function register($customer_id,$pincode,$device_id=null);

    /**
     * Update current pincode
     * @param integer $customer_id
     * @param string $device_id
     * @param string $old_pincode
     * @param string $new_pincode
     * @return mixed
     */
    public function update($customer_id,$new_pincode,$old_pincode = false,$device_id=null);

    /**
     * reset
     * @param integer $customer_id
     * @return mixed
     */
    public function reset($customer_id);
}