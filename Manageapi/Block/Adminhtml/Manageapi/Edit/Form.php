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

namespace Icare\Manageapi\Block\Adminhtml\Manageapi\Edit;


class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
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
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
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
        $this->setId('manageapi_form');
        $this->setTitle(__('Manage Api Information'));
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Icare\Installment\Model\Installment $model */
        $model = $this->_coreRegistry->registry('icare_manageapi');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setHtmlIdPrefix('manageapi_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'connect_url',
            'text',
            ['name' => 'connect_url', 'label' => __('Connect Url'), 'title' => __('Connect Url'), 'required' => true]
        );

        $fieldset->addField(
            'api_url',
            'select',
            [
                'name' => 'api_url',
                'label' => __('Api Url'),
                'value'=>'',
                'values' =>$this->getListApi(),
                'required' => true
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * get array current api
     */
    public function getListApi(){
        $data = [];
        foreach (glob(BP . "/app/code/Icare/*/etc/webapi.xml") as $webapixml) {
            $module = preg_replace('(app/code/Icare/(.*)/etc/webapi.xml)', '$1', $webapixml);
            $xml = simplexml_load_file($webapixml);
            if($xml){
                $data[$module]['label'] = $module;
                $urlApi = [];
                $Datavalue = [];
                foreach ($xml as $key => $value) {
                    $attr = $value->attributes();
                    $method = (string) $attr["method"];
                    $url = (string) $attr["url"];
                    $line = "    ".(str_pad($method, 6))." ".$url;
                    $urlApi['label'] = $line;
                    $urlApi['value'] = $url;
                    $Datavalue[] = $urlApi;
                }
                $data[$module]['value'] = $Datavalue;

            }
            if(empty($data[$module]['value'])){
                unset($data[$module]);
            }

        }

        return $data;
    }
}