<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:40
 */

namespace Icare\IcareOrderApi\Model;

use Icare\IcareOrderApi\Api\Data\GetOrderListInfoInterface;

class GetOrderInfo implements GetOrderListInfoInterface
{

    private $userId;
    private $pageSize;
    private $pageNum;
    private $from;
    private $to;
    private $status;
    private $organizationId;


    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getPageNum()
    {
        return $this->pageNum;
    }

    public function setPageNum($pageNum)
    {
        $this->pageNum = $pageNum;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return string[]
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string[] $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }
}