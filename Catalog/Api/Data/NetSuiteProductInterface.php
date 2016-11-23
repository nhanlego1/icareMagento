<?php 

namespace Icare\Catalog\Api\Data;

use Icare\Catalog\Api\Data\NetSuiteProductOptionInterface;

/**
 * Skeleton for to capture details of a product from NetSuite
 * @author Nam Pham
 *
 */
interface NetSuiteProductInterface {
    
    /**
     * @return string
     */
    public function getId();
    
    /**
     * @return string
     */
    public function getSku();
    
    
    /**
     * @return string
     */
    public function getName();
    
    
    /**
     * @return int
     */
    public function getStatus();
    
    /**
     * @return int
     */
    public function getCreatedAt();
    
    
    /**
     * @return int
     */
    public function getUpdatedAt();
    
    /**
     * @return \Icare\Catalog\Api\Data\NetSuiteProductOptionInterface[]|null
     * @see NetSuiteProductOptionInterface
     */
    public function getOptions();
    
    /**
     * @return string[]
     */
    public function getOptionMatrix();
    
    /**
     * @param string $value
     */
    public function setId(string $value);
    
    /**
     * @param string $value
     */
    public function setSku(string $value);
    
    
    /**
     * @param string $value
     */
    public function setName(string $value);
    
    
    /**
     * @param int $value
     */
    public function setStatus(int $value);
    
    /**
     * @param int $value
     */
    public function setCreatedAt(int $value);
    
    
    /**
     * @param int $value
     */
    public function setUpdatedAt(int $value);
    
    /**
     * @param \Icare\Catalog\Api\Data\NetSuiteProductOptionInterface[] $value
     * @see NetSuiteProductOptionInterface
     */
    public function setOptions($value);
    
    /**
     * @param string[] $value
     */
    public function setOptionMatrix($value);

    /**
     * @param string[] $optionSku
     * @return mixed
     */
    public function setOptionSku($optionSku);

    /**
     * @return string[]
     */
    public function getOptionSku();
}