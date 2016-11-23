<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/2/2016
 * Time: 5:30 PM
 */
namespace Icare\Gps\Api;

interface GpsInterface {

    /**
     * Get infor order
     * @param int $userId
     * @param string $lat
     * @param string $long
     * @param string $orderId
     * @return array
     */
    public function gpsTrack($userId, $lat, $long, $orderId = 0);

}