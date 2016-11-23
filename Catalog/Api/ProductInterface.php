<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Icare\Catalog\Api;

use Icare\Catalog\Api\Data\BestSellerParamInterface;
use Icare\Catalog\Api\Data\NetSuiteProductInterface;
use Icare\Catalog\Api\Data\NewestProductParamInterface;

interface ProductInterface
{
    /**
     * Get Content of Week
     * @param string $storeId
     * @return mixed
     */
    public function getContentOfWeek($storeId);
    
    /**
     * create or update product information. This function is declared for invocation from NetSuite.
     * @param NetSuiteProductInterface $product
     * @return mixed
     */
    public function saveNetSuiteProduct(NetSuiteProductInterface $product);

    /**
     * Get Best Seller Product
     * @param BestSellerParamInterface $bestSellerParam
     * @return mixed
     */
    public function getBestSeller(BestSellerParamInterface $bestSellerParam);

    /**
     * Get Newest Product
     * @param NewestProductParamInterface $newestProductParam
     * @return mixed
     */
    public function getNewestProduct(NewestProductParamInterface $newestProductParam);
}


