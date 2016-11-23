<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 14:00
 */

namespace Icare\Deposit\Block\Adminhtml\Payment\Create;


use Magento\Framework\App\ObjectManager;

class Form extends  \Magento\Backend\Block\Widget\Form\Generic{
    
    /**
     * @var \Magento\Backend\Model\Auth\Session $authSession
     */
    private $_authSession = NULL;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
        
    ) {
        $this->_authSession = $authSession;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Retrieve template object
     *
     * @return \Magento\Newsletter\Model\Template
     */
    public function getModel()
    {
        //$model = $this->_coreRegistry->registry('icare_deposit_payment');
        $model = $this->_backendSession->getData('deposit_payment_model');
        if(empty($model)){
            $model = ObjectManager::getInstance()->create('Icare\Deposit\Model\Payment');
            $this->_coreRegistry->register('icare_deposit_payment',$model);
        }
        return $model;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'icare_deposit_payment_form', 'action' => $this->getData('action'), 'method' => 'post','enctype'=>'multipart/form-data']]
        );

        $form->addField('user_id','hidden',[
            'name' => 'user_id',
            'value' => $this->getRequest()->getParam('user_id')
        ]);
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __(' ')]
        );

        $fieldset->addField(
            'transaction_date',
            'date',
            [
                'name' => 'transaction_date',
                'label' => __('Transaction date'),
                'title' => __('Transaction date'),
                'required' => true,
                'format' => 'Y-m-d',
                'date_format' => 'yyyy-MM-dd',
                'class' => 'input-text admin__control-text',
                'style' => 'width:350px',
                'value' => $model->getData('transaction_date')
            ]
        );
        $fieldset->addField(
            'transaction_amount',
            'text',
            [
                'label' => __('Transaction amount'),
                'required' => true,
                'name' => 'transaction_amount',
                'class' => 'input-text admin__control-text validate-zero-or-greater',
                'value' => $model->getData('transaction_amount')
            ]
        );

        $fieldset->addField(
            'payment_type',
            'select',
            [
                'label' => __('Payment Type'),
                'required' => true,
                'name' => 'payment_type',
                'class' => 'input-select admin__control-select',
                'values' => [
                    'auto_deduct' => 'Auto deduct',
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer'
                ]
            ]
        );
        $fieldset->addField(
            'account',
            'text',
            [
                'label' => __('Bank Account #'),
                'required' => false,
                'name' => 'account',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('account')
            ]
        );

        $fieldset->addField(
            'check',
            'text',
            [
                'label' => __('Check #'),
                'required' => false,
                'name' => 'check',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('check')
            ]
        );
        $fieldset->addField(
            'routing_code',
            'text',
            [
                'label' => __('Routing code'),
                'required' => false,
                'name' => 'routing_code',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('routing_code')
            ]
        );
        $fieldset->addField(
            'receipt',
            'text',
            [
                'label' => __('Receipt #'),
                'required' => false,
                'name' => 'receipt',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('receipt')
            ]
        );

        $fieldset->addField(
            'bank',
            'text',
            [
                'label' => __('Bank #'),
                'required' => false,
                'name' => 'bank',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('bank')
            ]
        );

        $fieldset->addField(
            'note',
            'textarea',
            [
                'label' => __('Note'),
                'required' => false,
                'name' => 'note',
                'class' => 'input-text admin__control-text',
                'value' => $model->getData('note')
            ]
        );
        $fieldset->addField(
            'attach_file',
            'file',
            [
                'label' => __('Attach file'),
                'required' => false,
                'name' => 'attach_file',
                'class' => 'input-text admin__control-text',
                'value' => ''
            ]
        );

       /* $fieldset2->addField(
            'submit',
            'submit',
            [
                'class' => 'action-default scalable save primary submit',
                'value' => 'Save payment',
                'name'  => 'btn-save-payment'
            ]
        );*/







        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);



        return parent::_prepareForm();
    }
}