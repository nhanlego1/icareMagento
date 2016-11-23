<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 3:16 PM
 */

namespace Icare\Catalog\Block\Adminhtml\Product\Edit;


class Tabs extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{

    protected function _prepareLayout() {
        $hideAttributes = ['quantity_and_stock_status', 'manufacturer'];
        $notRequiredAtt = [];
        $product = $this->getProduct();
        $editableAttributes = $product->getTypeInstance()->getEditableAttributes($product);
        $storeId = $this->_request->getParam('store');
        if (!$storeId) {
            $notRequiredAtt = ['price', 'tax_class_id'];
        }

        foreach ($hideAttributes as $attributeCode) {
            if (isset($editableAttributes[$attributeCode]) && $editableAttributes[$attributeCode]->getIsVisible()) {
                $editableAttributes[$attributeCode]->setIsVisible(false);
            }
        }
        foreach ($notRequiredAtt as $attributeCode) {
            if (isset($editableAttributes[$attributeCode]) && $editableAttributes[$attributeCode]->getIsVisible()) {
                $editableAttributes[$attributeCode]->setIsRequired(0);
            }
        }

        $layout = parent::_prepareLayout();
        $this->addTab(
            'installments',
            [
                'label' => __('Installments'),
                'content' => $this->_translateHtml(
                    $this->getLayout()->createBlock(
                        'Icare\Catalog\Block\Adminhtml\Product\Edit\Tab\Installments'
                    )->toHtml()
                ),
                'group_code' => self::BASIC_TAB_GROUP_CODE
            ]
        );
        return $layout;
    }
}