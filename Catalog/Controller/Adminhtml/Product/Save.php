<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 4:30 PM
 */

namespace Icare\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository) {
        parent::__construct($context, $productBuilder, $initializationHelper, $productCopier, $productTypeManager, $productRepository);
    }

    public function execute() {
        /**
         * Check current user permission
         * if this user assigned to a store (that mean he is not a special user)
         * add installment for this store only
         * unless apply for all store (required for all)
         */
        $objectManager = ObjectManager::getInstance();
        $block = $objectManager->get('Icare\Catalog\Block\Adminhtml\Product\Edit\Tab\Installments');
        $userStoreId = $block->getCurrentStoreId();



        $data = $this->getRequest()->getPostValue();
        $productId = $this->getRequest()->getParam('id');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        $storeView = $this->_request->getParam('store');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // get installment data
            if ($data) {
                $price = (int)$data['product']['price'];
                if (empty($price) && $storeView) {
                    $this->messageManager->addError(__('Price must is a number greater than 0'));
                    $this->_session->setProductData($data);
                    $redirectBack = $productId ? true : 'new';
                }
                else if (isset($data['product']['installments'])) {
                    // get all store id
                    /**
                     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
                     */
                    $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
                    $stores = $storeManager->getStores();
                    $storeIds = array();
                    foreach($stores as $store) {
                        if($userStoreId && $userStoreId != $store->getId())
                            continue;
                        $storeIds[] = $store->getId();
                    }


                    $selectedInstallments = $data['product']['installments'];
                    $insertData = array();
                    $selectedIds = array();
                    foreach($selectedInstallments as $storeId => $installments) {
                        $selectedIds[] = $storeId;
                        foreach ($installments as $installment) {
                            $insertData[] = ['product_id' => $productId, 'store_id' => $storeId, 'installment_id' => $installment];
                        }
                    }
                    if (count($selectedIds) == count($storeIds)) {
                        // remove all installment;
                        /**
                         * @var \Magento\Framework\App\ResourceConnection $resource
                         */
                        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                        /**
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface
                         */
                        $connection = $resource->getConnection();
                        $deleteCondition = ['product_id = ?' => $productId];
                        $connection->delete('icare_installment_product_relation', $deleteCondition);

                        $resultRedirect = parent::execute();

                        $connection->insertMultiple('icare_installment_product_relation', $insertData);
                    } else {
                        // installment must be require
                        $this->messageManager->addError(__('Alll Stores must have installment'));
                        $this->_session->setProductData($data);
                        $redirectBack = $productId ? true : 'new';
                    }

                } else {
                    $resultRedirect = parent::execute();
                    // installment must be require
//                    $this->messageManager->addError(__('Product must have installment'));
//                    $this->_session->setProductData($data);
//                    $redirectBack = $productId ? true : 'new';
                }
            }
        } catch (\Exception $ex) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($ex);
            $this->messageManager->addError($ex->getMessage());
            $this->_session->setProductData($data);
            $redirectBack = $productId ? true : 'new';
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }

        return $resultRedirect;
    }
}