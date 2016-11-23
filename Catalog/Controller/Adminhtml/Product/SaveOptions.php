<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */
namespace Icare\Catalog\Controller\Adminhtml\Product;

use Icare\Catalog\Model\Product;

class SaveOptions extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $productBuilder);
    }

    public function execute()
    {
        $optionCheck = $this->getRequest()->getParam('option_active');
        $product_id = $this->getRequest()->getParam('product_id');
        $store_id = $this->getRequest()->getParam('store_id');
        if ($optionCheck) {
            /**
             * @var \Magento\Framework\App\ResourceConnection $resource
             */
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

            /**
             * @var \Magento\Framework\DB\Adapter\AdapterInterface
             */
            $connection = $resource->getConnection();
            $whereCondition = ['product_id = ?' => $product_id, 'store_id = ?' => $store_id];
            $connection->update(Product::NETSUITE_CATALOG_PRODUCT_OPTION, ['active' => false], $whereCondition);

            // active true for post data
            $connection->update(Product::NETSUITE_CATALOG_PRODUCT_OPTION, ['active' => true],
                ['id in (?)' => array_keys($optionCheck)]);

            $result = ['error' => false, 'message' =>__('Update Success') ];
        } else {
            $result = ['error' => true, 'message' =>__('Update Failed') ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }
}
