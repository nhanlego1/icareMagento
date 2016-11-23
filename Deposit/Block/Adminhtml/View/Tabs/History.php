<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 11:36
 */

namespace Icare\Deposit\Block\Adminhtml\View\Tabs;


use Magento\Backend\Block\Template;
use Magento\Backend\Block\Text\ListText;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManager;

class History extends ListText implements TabInterface{

    /**
     * @var \Magento\Store\Model\StoreManager|null
     */
    protected $_storeManager = null;

    protected $helper = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data,

        StoreManager $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    protected $_template = 'view/tabs/history.html';
    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel() {
        // TODO: Implement getTabLabel() method.
        return __('Payment History');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle() {
        // TODO: Implement getTabTitle() method.
        return __('Payment History');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab() {
        // TODO: Implement canShowTab() method.
        return TRUE;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden() {
        // TODO: Implement isHidden() method.
        return FALSE;
    }

    public function getAllPayments(){
        $om = ObjectManager::getInstance();
        /**@var \Icare\Deposit\Model\ResourceModel\Payment\Collection $collection**/
        $collection = $om->create('Icare\Deposit\Model\ResourceModel\Payment\Collection');
        $user_id = $this->getRequest()->getParam('user_id');
        $collection->addFieldToFilter('user_id',$user_id);
        return $collection->load();
    }

    public function getCurrencyCode(){
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

}