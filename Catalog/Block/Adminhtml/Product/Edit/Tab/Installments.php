<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 3:22 PM
 */

namespace Icare\Catalog\Block\Adminhtml\Product\Edit\Tab;


use Icare\Installment\Model\Installment;
use Icare\User\Model\RoleConstant;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

class Installments extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * @var string
     */
    protected $_storeFromHtml;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/installments.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Icare\Installment\Model\ResourceModel\Installment\CollectionFactory
     */
    protected $_installmentCollectionFactory = null;

    /**
     * @var \Icare\Custom\Helper\ICareHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Icare\Installment\Model\ResourceModel\Installment\CollectionFactory $installmentCollectionFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_request = $context->getRequest();
        $this->_coreRegistry = $coreRegistry;
        $this->_installmentCollectionFactory = $installmentCollectionFactory;
        $this->_authSession = $authSession;
        $this->_storeManager = $context->getStoreManager();
        $this->helper =  ObjectManager::getInstance()->get('\Icare\Custom\Helper\ICareHelper');;
        parent::__construct($context, $websiteFactory, $storeGroupFactory, $storeFactory, $data);
    }


    /**
     * Retrieve edited product model instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get store ID of current product
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    /**
     * Get ID of current product
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    public function getWebsiteCollection()
    {
        $collection = $this->_websiteFactory->create()->getResourceCollection();

        $store = $this->getCurrentStore();
        if ($store) {
            $websiteId = $store->getWebsiteId();
            $collection->addIdFilter($websiteId);
        }
        return $collection->load();
    }

    public function getCurrentStoreId() {
        $user = $this->_authSession->getUser();
        $storeId = null;
        if($user){
            if (!$this->helper->checkSpecialUser($user) && !$this->_storeManager->isSingleStoreMode()) {
                $storeId = $user->getStoreId();
            }
        }

        return $storeId;
    }

    public function isAllStoreViews() {
        $store = $this->_request->getParam('store');

        if ($store) {
            return FALSE;
        }
        return TRUE;
    }

    public function getCurrentStore() {
        $storeId = $this->getCurrentStoreId();
        if (isset($storeId)) {
            return $this->_storeManager->getStore($storeId);
        }
        return null;
    }

    public function getAvailableInstallments() {
        $installments = $this->_installmentCollectionFactory->create()
            ->addFieldToSelect('installment_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('number_of_repayment')
            ->addFieldToFilter('is_active', ['eq' => Installment::STATUS_ENABLED]);
        return $installments->load();
    }

    public function getSelectedInstallments() {
        $store_id = $this->getCurrentStoreId();

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from("icare_installment_product_relation")
            ->where('product_id = ?', $this->getProductId());
        if ($store_id) {
            $select->where('store_id = ?', $store_id);
        }

        $rows = $connection->fetchAssoc($select);
        $selectedInstallments = array();
        foreach ($rows as $row) {
            $selectedInstallments[$row['store_id']][$row['installment_id']] = true;
        }
        return $selectedInstallments;
    }

    

    /**
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeGroupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    /**
     * @param int|\Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_websiteFactory->create()->load($website);
        }
        $store_id = $this->getCurrentStoreId();
        if($store_id){
            return $this->_storeGroupFactory->create()->getCollection()->addFieldToFilter('default_store_id',$store_id)->setLoadDefault(true);
        }
        return parent::getGroupCollection($website);

    }
}