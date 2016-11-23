<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * customers defined options
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Icare\Catalog\Block\Adminhtml\Product\Edit\Tab;

use Icare\Catalog\Model\Product;
use Magento\Backend\Block\Widget;

class Options extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/options.phtml';

    protected $_productInstance;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @return Widget
     */
    protected function _prepareLayout()
    {
        $this->_coreRegistry = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Registry');
        return parent::_prepareLayout();
    }

    public function getOptions() {

        if (!$this->getCurrentStoreId()) {
            return null;
        }
        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from(Product::NETSUITE_CATALOG_PRODUCT_OPTION)
            ->where('product_id = ?', $this->getCurrentProductId())
            ->where('store_id = ?', $this->getCurrentStoreId());
        $rows = $connection->fetchAssoc($select);
        $options = $this->getProduct()->getOptions();
        $renderOptions = array();
        foreach ($options as $option) {
            $renderOptions['option_title'][] = $option->getStoreTitle() ? $option->getStoreTitle() : $option->getTitle();
            foreach ($option->getValues() as $optionType) {
                foreach ($rows as $row) {
                    $optionTypeIdArray = explode('|', $row['option_type_id_array']);
                    foreach ($optionTypeIdArray as $optionTypeId) {
                        if ($optionTypeId == $optionType->getOptionTypeId()) {
                            $renderOptions['options'][$row['id']]['option_values'][] = $optionType->getStoreTitle() ? $optionType->getStoreTitle() : $optionType->getTitle();
                        }
                    }
                    $renderOptions['options'][$row['id']]['active'] = $row['active'];
                }
            }
        }
        return $renderOptions;
    }

    public function getUpdateProductOptionUrl() {
        try {
            return $this->getUrl('*/*/saveoptions') . '?isAjax=true';
        } catch (\Exception $ex) {
            print_r($ex->getTraceAsString());
            die;
        }

    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    public function getCurrentProductId() {
        return $this->getProduct()->getId();
    }

    public function getProduct()
    {
        if (!$this->_productInstance) {
            $product = $this->_coreRegistry->registry('product');
            if ($product) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = $this->_product;
            }
        }

        return $this->_productInstance;
    }

}
