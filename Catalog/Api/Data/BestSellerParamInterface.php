<?php
/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 11/16/16
 * Time: 10:37 AM
 */

namespace Icare\Catalog\Api\Data;


interface BestSellerParamInterface
{
    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);
}