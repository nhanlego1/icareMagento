<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/21/16
 * Time: 8:55 AM
 */

namespace Icare\Sales\Model\Order;


class Shipment extends \Magento\Sales\Model\Order\Shipment
{
    const STATUS_SHIPPED = 2;
    const STATUS_DELIVERED = 3;
    const STATUS_DELIVERY_FAILED = 4;
}