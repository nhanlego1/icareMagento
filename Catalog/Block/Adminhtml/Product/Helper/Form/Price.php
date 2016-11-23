<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 04/11/2016
 * Time: 17:45
 */

namespace Icare\Catalog\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form price field helper
 */
class Price extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addClass('validate-greater-than-zero');
    }
}