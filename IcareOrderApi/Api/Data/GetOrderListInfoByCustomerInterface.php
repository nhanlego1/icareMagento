<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:34
 */
namespace Icare\IcareOrderApi\Api\Data;

interface GetOrderListInfoByCustomerInterface
{

    /**
     * @return int
     */
    public function getCustomerid();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerid($customerId);

    /**
     * @return int
     */
    public function getPagesize();

    /**
     * @param $pageSize
     * @return mixed
     */
    public function setPagesize($pageSize);

    /**
     * @return int
     */
    public function getPagenum();

    /**
     * @param $pageNum
     * @return mixed
     */
    public function setPagenum($pageNum);


}

?>