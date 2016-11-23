<?php
/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 11/16/16
 * Time: 10:39 AM
 */

namespace Icare\Catalog\Model;


use Icare\Catalog\Api\Data\BestSellerParamInterface;

class BestSellerParam implements BestSellerParamInterface
{
    protected $storeId;

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }
}