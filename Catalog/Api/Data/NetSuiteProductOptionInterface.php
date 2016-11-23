<?php

namespace Icare\Catalog\Api\Data;

/**
 * Skeleton for to capture details of a product option from NetSuite
 * @author Nam Pham
 *
 */
interface NetSuiteProductOptionInterface {
    
    /**
     * @return string
     */
    public function getOptionKey();
    
    /**
     * 
     * @param string $value
     */
    public function setOptionKey(string $value);
    
    
    /**
     * @return string
     */
    public function getTitle();
    
    /**
     *
     * @param string $value
     */
    public function setTitle(string $value);
    
    /**
     * @return string[]
     */
    public function getValues();
    
    /**
     *
     * @param string[] $value
     */
    public function setValues($value);
}