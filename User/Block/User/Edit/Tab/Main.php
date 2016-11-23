<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Icare\User\Block\User\Edit\Tab;

class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    
    protected function _prepareForm()
    {
        $rs = parent::_prepareForm();
        
        // Get Store Manager
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\StoreManagerInterface|\Magento\Store\Model\StoreManager $storeManager */
        $systemStore = $om->get('\Magento\Store\Model\System\Store');
        $form = parent::getForm();
        $fieldset = $form->getElement('base_fieldset');
        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'store_id',
                'label' => __('Store'),
                'title' => __('Store'),
                'required' => true,
                'values' => $systemStore->getStoreValuesForForm(),
                'class' => 'select'
            ]
        );
        $fieldset->addField(
            'is_allowed_confirm_order',
            'select',
            [
                'name' => 'is_allowed_confirm_order',
                'label' => __('Allowed Auto Confirm Order'),
                'title' => __('Allowed Auto Confirm Order'),
                'values' => [
                    [
                        'label'=> __('Disabled'),
                        'value' => 0
                    ],
                    [
                        'label'=> __('Enabled'),
                        'value' => 1
                    ]
                ],
                'required' => false
            ]
        );

        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_user');

        $form->addValues(['store_id' => $model->getStoreId()]);
        $form->addValues(['is_allowed_confirm_order' => $model->getIsAllowedConfirmOrder()]);

        $this->setForm($form);

        return $rs;
    }
}
