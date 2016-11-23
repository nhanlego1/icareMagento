<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/4/16
 * Time: 6:09 PM
 */

namespace Icare\Sales\Block\Adminhtml\ICareCenter\Edit;


class SaveButton extends \Magento\Customer\Block\Adminhtml\Edit\SaveButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $canModify = !$customerId || !$this->customerAccountManagement->isReadonly($this->getCustomerId());
        $data = [];
        if ($canModify) {
            $data = [
                'label' => __('Save iCare Center'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 90,
            ];
        }
        return $data;
    }
}