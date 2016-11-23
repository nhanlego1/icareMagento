<?php
/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 11/16/16
 * Time: 11:13 AM
 */

namespace Icare\Catalog\Api\Data;


interface NewestProductParamInterface
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