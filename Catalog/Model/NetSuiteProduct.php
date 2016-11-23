<?php 

namespace Icare\Catalog\Model;

use Icare\Catalog\Api\Data\NetSuiteProductInterface;
use Icare\Catalog\Api\Data\NetSuiteProductOptionInterface;

/**
 * implemetation of {@link NetSuiteProductInterface}
 * @author Nam Pham
 *
 */
class NetSuiteProduct implements NetSuiteProductInterface {
    
    private $_id;
    
    private $_sku;
    
    private $_name;
    
    private $_status;
    
    private $_options;
    
    private $_optionMatrix;
    
    private $_createdAt;
    
    private $_updatedAt;

    private $_optionSku;
    
    
    /**
     * @return string
     */
    public function getId() {
        return $this->_id;
    }
    
    /**
     * @return string
     */
    public function getSku() {
        return $this->_sku;
    }
    
    
    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }
    
    
    /**
     * @return int
     */
    public function getStatus() {
        return $this->_status;
    }
    
    /**
     * @return int
     */
    public function getCreatedAt() {
        return $this->_createdAt;
    }
    
    
    /**
     * @return int
     */
    public function getUpdatedAt() {
        return $this->_updatedAt;
    }
    
    /**
     * @return \Icare\Catalog\Api\Data\NetSuiteProductOptionInterface[]|null
     */
    public function getOptions() {
        return $this->_options;
    }
    
    /**
     * @return string[]
     */
    public function getOptionMatrix() {
        return $this->_optionMatrix;
    }
    
    /**
     * @param string $value
     */
    public function setId(string $value) {
        $this->_id = $value;
    }
    
    /**
     * @param string $value
     */
    public function setSku(string $value) {
        $this->_sku = $value;
    }
    
    
    /**
     * @param string $value
     */
    public function setName(string $value) {
        $this->_name = $value;
    }
    
    
    /**
     * @param int $value
     */
    public function setStatus(int $value) {
        $this->_status = $value;
    }
    
    /**
     * @param int $value
     */
    public function setCreatedAt(int $value) {
        $this->_createdAt = $value;
    }
    
    
    /**
     * @param int $value
     */
    public function setUpdatedAt(int $value) {
        $this->_updatedAt = $value;
    }
    
    /**
     * @param \Icare\Catalog\Api\Data\NetSuiteProductOptionInterface[] $value
     */
    public function setOptions($value) {
        $this->_options = $value;
    }
    
    /**
     * @param string[] $value
     */
    public function setOptionMatrix($value) {
        $this->_optionMatrix = $value;
    }

    public function setOptionSku($optionSku)
    {
        $this->_optionSku = $optionSku;
    }

    public function getOptionSku()
    {
        return $this->_optionSku;
    }
}