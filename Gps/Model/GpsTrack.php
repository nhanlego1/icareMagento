<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 10/24/16
 * Time: 3:57 PM
 */

namespace Icare\Gps\Model;

use Icare\Gps\Api\GpsInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class GpsTrack implements GpsInterface{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\App\Helper\Context $context)
    {
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
    }
    /**
     * api track gps
     * @param int $userId
     * @param string $lat
     * @param string $long
     * @param string $orderId
     * @return mixed
     */
    public function gpsTrack($userId, $lat, $long, $orderId = 0)
    {
        // TODO: Implement gpsTrack() method.
        $result = [];
        $logger = '';
        if(empty($userId) || !is_numeric($userId)){
            $result[] = new IcareException(__("User Id is required."));
            $logger .= __("user id is required.");
        }
        if(empty($lat)){
            $result[] = new IcareException(__("Latitude is required."));
            $logger .= __("Latitude is required.");
        }
        if(empty($long)){
            $result[] = new IcareException(__("Longitude is required."));
            $logger .= __("Longitude is required.");
        }
        if($result){
            $this->_logger->error($logger);
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $gps = $om->create('Icare\Gps\Model\Gps');
        $gps->setUserId($userId);
        $gps->setLat($lat);
        $gps->setLong($long);
        $gps->setOrderId($orderId);
        try{
            $gps->save();
            $status = [];
            $status['message'] = __("Update GPS location success.");
            $status['code'] = 200;
            $status['status'] = true;
            return [$status];
        }catch(\Exception $ex){
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
    }
}