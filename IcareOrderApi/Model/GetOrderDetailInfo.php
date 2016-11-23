<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:40
 */

namespace Icare\IcareOrderApi\Model;

use Icare\IcareOrderApi\Api\Data\GetOrderDetailInfoInterface;

class GetOrderDetailInfo implements GetOrderDetailInfoInterface
{
    private $orderId;
    private $orderIncrementId;

    /**
     * @return int
     */
    public function getOrderid() {
        return $this->orderId;
    }

    /**
     * @param int $orderId 
     */
    public function setOrderid($orderId) {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getOrderincrementid() {
        return $this->orderIncrementId;
    }
    
    /**
     * @param string $orderIncrementId 
     */
    public function setOrderincrementid($orderIncrementId) {
        $this->orderIncrementId = $orderIncrementId;
    }
}