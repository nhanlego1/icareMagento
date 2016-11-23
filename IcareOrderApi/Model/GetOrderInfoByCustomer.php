<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:40
 */

namespace Icare\IcareOrderApi\Model;

use Icare\IcareOrderApi\Api\Data\GetOrderListInfoByCustomerInterface;

class GetOrderInfoByCustomer implements GetOrderListInfoByCustomerInterface
{

    private $customerId;
    private $pageSize;
    private $pageNum;


    public function getCustomerid()
    {
        return $this->customerId;
    }

    public function setCustomerid($customerId)
    {
        $this->customerId = $customerId;
    }

    public function getPagesize()
    {
        return $this->pageSize;
    }

    public function setPagesize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getPagenum()
    {
        return $this->pageNum;
    }

    public function setPagenum($pageNum)
    {
        $this->pageNum = $pageNum;
    }
}