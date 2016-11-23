<?php
/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 11/16/16
 * Time: 11:14 AM
 */

namespace Icare\Catalog\Model;


use Icare\Catalog\Api\Data\NewestProductParamInterface;

class NewestProductParam implements NewestProductParamInterface
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