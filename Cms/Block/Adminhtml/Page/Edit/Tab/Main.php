<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 8/22/16
 * Time: 2:33 PM
 */
namespace Icare\Cms\Block\Adminhtml\Page\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Cms\Block\Adminhtml\Page\Edit\Tab\Main
{
    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_resource = $resource;
        parent::__construct($context, $registry, $formFactory, $systemStore, $data);
    }

    /**
     * Returns table name
     *
     * @param string|array $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->_resource->getTableName($name);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $rs = parent::_prepareForm();
        $model = $this->_coreRegistry->registry('cms_page');
        $form = parent::getForm();
        $fieldset = $form->addFieldset('category_content', ['legend' => __('Category content')]);

        if ($model->getId()) {
            $storeElement = $form->getElement('store_id');
            $storeElement->setReadonly(true, true);

            $tableRating = $this->getTable('icare_customer_page_rating');
            $connection = $this->_resource->getConnection();

            $select = $connection->select()->from(
                $tableRating,
                ['COUNT(id) AS voted', 'SUM(rating) AS scores']
            )->where(
                "{$tableRating}.page_id = :page_id"
            )->group(
                "{$tableRating}.page_id"
            );

            $binds = ['page_id' => $model->getId()];
            $rating = $connection->fetchAll($select, $binds);
            $rating = reset($rating);
        }

        $field = $fieldset->addField(
            'category',
            'select',
            [
                'name' => 'category',
                'label' => __('Choose category'),
                'title' => __('Choose category'),
                'required' => false,
                'values' => array(
                    0 => __('None'),
                    1 => __('Content Category'),
                    2 => __('Installment'),
                    3 => __('Sales Order'),
                    4 => __('Credit'),
                    5 => __('Security'),
                    6 => __('Reset Pincode'),
                    7 => __('System Support'),
                    8 => __('Customer Experience')
                )
            ]
        );

        $field = $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => __('SKU'),
                'title' => __('SKU'),
                'required' => false
            ]
        );

        $field = $fieldset->addField(
            'variable',
            'multiselect',
            [
                'name' => 'variable[]',
                'label' => __('Params'),
                'values' =>$this->getListVariables(),
                'required' => false,
            ]
        );

        $field = $fieldset->addField(
            'like',
            'text',
            [
                'name' => 'like',
                'label' => __('Like'),
                'title' => __('Like'),
                'required' => false,
                'disabled' =>true,
            ]
        );

        $field = $fieldset->addField(
            'unlike',
            'text',
            [
                'name' => 'unlike',
                'label' => __('UnLike'),
                'title' => __('UnLike'),
                'required' => false,
                'disabled' =>true,
            ]
        );

        $form->setValues($model->getData());

        $fieldset->addField(
            'voted',
            'text',
            [
                'name' => 'voted',
                'value' => isset($rating) ? $rating['voted'] : '',
                'label' => __('Voted'),
                'title' => __('Voted'),
                'required' => false,
                'disabled' =>true,
            ]
        );

        $fieldset->addField(
            'scores',
            'text',
            [
                'name' => 'scores',
                'value' => isset($rating) ? $rating['scores'] : '',
                'label' => __('Scores'),
                'title' => __('Scores'),
                'required' => false,
                'disabled' =>true,
            ]
        );

        $this->setForm($form);
        return $rs;
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $this->_coreRegistry->registry('cms_page');
        if($model->getId()){
            if(!empty($model->getVariable()) && strpos($model->getVariable(),',') !== false){
                $variable = explode(',',$model->getVariable());
                $this->getForm()->addValues(array('variable'=>$variable));
            }
        }


        return parent::_initFormValues();
    }

    /**
     * Get list website
     * @return array
     */
    public function getListWebsite()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $websiteObject = $om->get('Magento\Store\Model\WebsiteRepository');
        $websites = $websiteObject->getList();
        $return = [];
        $return[0] = __('None');
        foreach($websites as $website){
            $return[$website->getWebsiteId()] = $website->getName();
        }
        return $return;
    }

    /**
     * get list variable that define in icare
     */
    protected function getListVariables(){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = [];
        $select = $connection->select()->from(
            'variable',
            ['variable_id']
        );
        $variables = $connection->fetchAll($select, $bind);
        $data = [];
        if($variables){

            foreach($variables as $variable){
                $var = $om->create('Magento\Variable\Model\Variable')->load($variable['variable_id']);
                if(strpos($var->getCode(),'cms_') !== false){
                    $data[] = ['label'=> str_replace('cms_','',$var->getCode()),'value' => $variable['variable_id']];
                }

            }

        }
        return $data;

    }
}