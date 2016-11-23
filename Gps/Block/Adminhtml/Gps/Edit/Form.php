<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 2:33 PM
 */

namespace Icare\Gps\Block\Adminhtml\Gps\Edit;


class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_userCollectionFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_userCollectionFactory = $userCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gps_form');
        $this->setTitle(__('Field Sale Location Information'));
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Icare\Installment\Model\Installment $model */
        $model = $this->_coreRegistry->registry('icare_gps');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setHtmlIdPrefix('gps_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getId()) {
            $fieldset->addField('gps_id', 'hidden', ['name' => 'gps_id']);
        }
        $fieldset->addField(
            'lat',
            'text',
            ['name' => 'lat', 'label' => __('Latitude'), 'title' => __('Latitude'), 'required' => true]
        );
        $fieldset->addField(
            'long',
            'text',
            ['name' => 'long', 'label' => __('Longitude'), 'title' => __('Longitude'), 'required' => true]
        );
        $fieldset->addField(
            'user_id',
            'select',
            [
                'label' => __('Field sales'),
                'title' => __('Field sales'),
                'name' => 'user_id',
                'required' => false,
                //'options' => [$this->getUserList()]
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get user list
     */
    public function getUserList(){
        $option = [];
        $collection = $this->_userCollectionFactory->create();
        foreach($collection as $item)
        {
            $option[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }
       // var_dump($option);die;
        return $option;
    }

}