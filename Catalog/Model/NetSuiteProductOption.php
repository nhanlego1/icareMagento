<?php

namespace Icare\Catalog\Model;

use Icare\Catalog\Api\Data\NetSuiteProductOptionInterface;

/**
 * Skeleton for to capture details of a product option from NetSuite
 * @author Nam Pham
 *
 */
class NetSuiteProductOption implements NetSuiteProductOptionInterface {
    
    private $_optionKey;
    
    private $_title;
    
    private $_values;
    
    /**
     * @return string
     */
    public function getOptionKey() {
        return $this->_optionKey;
    }
    
    /**
     * 
     * @param string $value
     */
    public function setOptionKey(string $value) {
        $this->_optionKey = $value;
    }
    
    
    /**
     * @return string
     */
    public function getTitle() {
        return $this->_title;
    }
    
    /**
     *
     * @param string $value
     */
    public function setTitle(string $value) {
        $this->_title = $value;
    }
    
    /**
     * @return string[]
     */
    public function getValues() {
        return $this->_values;
    }
    
    /**
     *
     * @param string[] $value
     */
    public function setValues($value) {
        $this->_values = $value;
    }
}