<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:34
 */
namespace Icare\IcareOrderApi\Api\Data;

interface GetOrderListInfoInterface
{

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @param int $userId
     * @return mixed
     */
    public function setUserId($userId);

    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @param int $pageSize
     * @return mixed
     */
    public function setPageSize($pageSize);

    /**
     * @return int
     */
    public function getPageNum();

    /**
     * @param int $pageNum
     * @return mixed
     */
    public function setPageNum($pageNum);

    /**
     * @return string
     */
    public function getFrom();

    /**
     * @param string $from
     * @return mixed
     */
    public function setFrom($from);

    /**
     * @return string
     */
    public function getTo();

    /**
     * @param string $to
     * @return mixed
     */
    public function setTo($to);

    /**
     * @return string[]
     */
    public function getStatus();

    /**
     * @param string[] $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getOrganizationId();

    /**
     * @param int $organizationId
     * @return mixed
     */
    public function setOrganizationId($organizationId);

}

