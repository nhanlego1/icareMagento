<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 29/07/2016
 * Time: 11:06
 */

namespace Icare\Catalog\Model;


use Icare\Catalog\Api\Data\BestSellerParamInterface;
use Icare\Catalog\Api\Data\NewestProductParamInterface;
use Icare\Catalog\Api\ProductInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Catalog\Api\Data\NetSuiteProductInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\SearchResultInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\ValidatorException;

class Product implements ProductInterface
{

    const NETSUITE_CATALOG_PRODUCT_OPTION = 'netsuite_catalog_product_option';
    /**#@+
     * Product Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 2;

    private $_productCollection;
    private $_storeManager;
    private $_localeDate;
    private $_productManager;
    
    private $_productFactory;
    private $_productRepo;

    /** @var \Psr\Log\LoggerInterface $_logger */
    private $_logger;

    /**
     * @var \Icare\Catalog\Api\Data\BestsellerSearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    private $productSearchResultFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $metadataService;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * Product constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $productManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     * @param \Icare\Catalog\Api\Data\BestsellerSearchResultsInterfaceFacetory $searchResultsFactory
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $productManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Icare\Catalog\Api\Data\BestsellerSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $productSearchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_productCollection = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_productManager = $productManager;
        $this->_productFactory = $productFactory;
        $this->_productRepo = $productRepo;
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
        $this->searchResultFactory = $searchResultsFactory;
        $this->productSearchResultFactory = $productSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->metadataService = $metadataServiceInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * getContentOfWeek
     * @param string $storeId
     * @return array|\Icare\Exception\Model\IcareWebApiException
     */
    public function getContentOfWeek($storeId)
    {
        $result = [];

        try {
            $todayStartOfDayDate = $this->_localeDate
                ->date($useTimezone = false)
                ->setTime(0, 0, 0)
                ->format('Y-m-d H:i:s');
            $todayEndOfDayDate = $this->_localeDate
                ->date($useTimezone = false)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            $collection = $this->_productCollection->create();
            $collection->addStoreFilter($storeId)
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('news_to_date')
                ->addAttributeToFilter([
                    ['attribute'=>'type_id', 'eq'=>\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL]
                ])
                ->addAttributeToFilter(
                    'news_from_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'to' => $todayEndOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left'
                )->addAttributeToFilter(
                    'news_to_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left'
                )->addAttributeToFilter(
                    [
                        ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                        ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                    ]
                )->addAttributeToSort(
                    'news_from_date',
                    'desc'
                )->setPageSize(
                    100
                )->setCurPage(
                    1
                );
            $collection->load();
            if (empty($collection)) {
                return $result;
            }
            $store = $this->_storeManager->getStore($storeId);
            foreach ($collection as $product) {
                $mediaGalleryData = [];
                $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'catalog/product' . $product->getImage();
                $product = $this->_productManager->load($product->getId());
                $data = $product->toArray();
                $gallery = $data['media_gallery']['images'];
                foreach ($gallery as $mediaGalleryImage) {
                    $mediaGalleryData[] = [
                        'mediaType' => $mediaGalleryImage['media_type'],
                        'videoUrl' => $mediaGalleryImage['video_url'],
                    ];
                }
                $item = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice(),
                    'image' => $imageUrl,
                    'description' => $product->getDescription(),
                    'expired_date' => strtotime($product->getNewsToDate()),
                    'media_content' => $mediaGalleryData
                ];

                $result[] = $item;
            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException($ex->getMessage());
            return new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        return $result;
    
    }
    
    /**
     * prepare product as it should be because the limitation of Mage in term of deserialization
     * @param NetSuiteProductInterface $product
     * @return void
     */
    private function prepareNetSuiteProduct(NetSuiteProductInterface $product)
    {
        if ($product->getOptionMatrix() != NULL) {
            $optionSku = [];
            $options = [];
            foreach ($product->getOptionMatrix() as $optStr) {
                $optArr = explode('|', $optStr);
                // remove the last item, it is a option sku
                $optionSku[] = array_pop($optArr);
                $options []= $optArr;
            }
            $product->setOptionMatrix($options);
            $product->setOptionSku($optionSku);
        }
    }
    
    /**
     * apply netsuite properties to a customizable option
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Icare\Catalog\Api\Data\NetSuiteProductOptionInterface $nsOption
     * @return  void
     */
    private function applyOptionProperties($option, $nsOption)
    {
        $option->setNetsuiteKey($nsOption->getOptionKey());
        $option->setTitle($nsOption->getTitle());
        $option->setType('drop_down');
        $option->setIsRequire(1);
        
        $values = [];
        
        // index saved value
        $mapSaved = [];
        if ($option->getId()!=null) {
            $valuesCol = $option->getValuesCollection();
            foreach ($valuesCol as $value) {
                $mapSaved[$value->getTitle()] = $value;
            }
        }
        
        // add values regarding to NetSuite orders
        foreach ($nsOption->getValues() as $nsOptValue) {
            if (isset($mapSaved[$nsOptValue])) {
                $value = $mapSaved[$nsOptValue];
                unset($mapSaved[$nsOptValue]);
            } else {
                $value = $option->getValuesCollection()->getNewEmptyItem();
                $value->setTitle($nsOptValue);
            }
            $value->setSortOrder(count($values) + 1);
            $values[] = $value;
            if ($value->getOptionTypeId() === null) {
                $option->getValueInstance()->addValue($value->getData(''));  // to be saved later
            }
        }
        $option->setValues($values);
        
        if (!empty($mapSaved)) {
            // remove discarded options
            foreach ($mapSaved as $key => $value) {
                $value->delete();
            }
        }
        
        // set values for validation
        $valValues = [];
        foreach ($values as $value) {
            $valValues[] = $value->getData('');
        }
        $option->setData('values', $valValues);
        
        // $option->save();   // option will be saved when product is save
        $option->getProduct()->getOptionInstance()->addOption($option->getData(''));  // to be saved later
    }
    
    /**
     * update Catalog product
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param NetSuiteProductInterface $product
     * @return void
     */
    private function applyProductProperties($catalogProduct, $product)
    {
        if ($catalogProduct->getId() == null) {
            $catalogProduct->setSku($product->getSku());
            $catalogProduct->setUrlKey($product->getSku());
        }
        $catalogProduct->setName($product->getName());
        $catalogProduct->setStatus($product->getStatus());
        //set manage stock is false
        $catalogProduct->setStockData(['use_config_manage_stock' => 0,
            'is_in_stock' => 1,
            'manage_stock' => 0,
            'use_config_notify_stock_qty' => 0]);

        // TODO setCreatedAt and setUpdatedAt
        
        $options = [];
        if ($product->getOptions() != null) {
            // map saved options my netsuite key
            $mapSaved = [];
            $listSaved = $catalogProduct->getOptions();
            if ($listSaved !== null && !empty($listSaved)) {
                foreach ($listSaved as $option) {
                    $mapSaved[$option->getNetsuiteKey()] = $option;
                }
            }
            
            // set product options
            $catalogProduct->setHasOptions(1);
            foreach ($product->getOptions() as $nsOption) {
                if (isset($mapSaved[$nsOption->getOptionKey()])) {
                    $option = $mapSaved[$nsOption->getOptionKey()];
                    unset($mapSaved[$nsOption->getOptionKey()]);
                } else {
                    $option = $catalogProduct->getProductOptionsCollection()->getNewEmptyItem();
                    $option->setProduct($catalogProduct);
                }
                $this->applyOptionProperties($option, $nsOption);
                $options []= $option;
            }
            
            if (!empty($mapSaved)) {
                // remove discarded options
                foreach ($mapSaved as $key => $option) {
                    $option->delete();
                }
            }
        } else {
            $catalogProduct->setHasOptions(0);
        }
        $catalogProduct->setOptions($options); // empty array will remove options

        // set default attribute set
        $catalogProduct->setAttributeSetId($catalogProduct->getDefaultAttributeSetId());
        
        $catalogProduct->save();
        
        return $catalogProduct;
    }

    /**
     * applyProductOptionByStore
     * @param mixed $catalogProduct
     * @param mixed $product
     * @return void
     */
    private function applyProductOptionByStore($catalogProduct, $product)
    {
        $productId = $catalogProduct->getId();
        $stores = $this->_storeManager->getStores();
        // reset all value for update
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $updateData = ['on_update' => false];
        foreach ($stores as $store) {
            $whereCondition = ['product_id = ?' => $productId, 'store_id = ?' => $store->getId()];
            $connection->update(self::NETSUITE_CATALOG_PRODUCT_OPTION, $updateData, $whereCondition);
        }
        $catalogProduct = $this->_productManager->load($productId);
        $options = $catalogProduct->getOptions();
        $mapOptionsTypeIdNetsuiteKey = [];
        if ($options) {
            foreach ($options as $option) {
                $netsuiteKey = $option->getNetsuiteKey();
                foreach ($option->getValues() as $optionType) {
                    $title = $optionType->getTitle();
                    $mapOptionsTypeIdNetsuiteKey[$netsuiteKey . '_' . $title] = $optionType->getOptionTypeId();
                }
            }
        }
        $productOptionsMatrix = $product->getOptionMatrix();
        $productOptionSku = $product->getOptionSku();
        if ($productOptionsMatrix) {
            $optionSkuIndex = 0;
            $netsuiteOptions = $product->getOptions();
            foreach ($productOptionsMatrix as $productOptionValues) {
                $optionSku = $productOptionSku[$optionSkuIndex++];
                $optionIndex = 0;
                $optionTypeArray = [];
                foreach ($productOptionValues as $optvalue) {
                    $netsuiteKey = $netsuiteOptions[$optionIndex++]->getOptionKey();
                    $optionTypeArray[] = $mapOptionsTypeIdNetsuiteKey[$netsuiteKey. '_'. $optvalue];

                }
                foreach ($stores as $store) {
                    $select = $connection->select()->from(
                        self::NETSUITE_CATALOG_PRODUCT_OPTION
                    )->where(
                        'product_id = ?',
                        $productId
                    )->where(
                        'store_id = ?',
                        $store->getId()
                    )->where(
                        'option_type_id_array = ?',
                        implode('|', $optionTypeArray)
                    );
                    $row = $connection->fetchRow($select);
                    if ($row) {
                        $connection->update(
                            self::NETSUITE_CATALOG_PRODUCT_OPTION, ['on_update' => true], ['id = ?' => $row['id']]
                        );
                    } else {
                        $insertValues = [
                            'product_id' => $productId,
                            'store_id' => $store->getId(),
                            'option_type_id_array' => implode('|', $optionTypeArray),
                            'on_update' => true,
                            'option_type_sku' => $optionSku
                        ];
                        $connection->insert(self::NETSUITE_CATALOG_PRODUCT_OPTION,
                            $insertValues);
                    }

                }

            }

        }

        // remove on_update = false
        $deleteCondition = ['product_id = ?' => $productId, 'on_update = ?' => false];
        $connection->delete(self::NETSUITE_CATALOG_PRODUCT_OPTION, $deleteCondition);
    }
    
    
    /**
     * 
     * {@inheritDoc}
     * @see \Icare\Catalog\Api\ProductInterface::saveNetSuiteProduct()
     */
    public function saveNetSuiteProduct(NetSuiteProductInterface $product)
    {
        $this->prepareNetSuiteProduct($product);
        $isNew = false;
        try {
            $catalogProduct = $this->_productRepo->get($product->getSku(), TRUE);
            $isNew = false;
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $this->_logger->info(__('creating new product %2 (%1)', [$product->getSku(), $product->getName()]));
            $catalogProduct = $this->_productFactory->create(['price' => 0]);
            $isNew = true;
        }
        
        // apply details from $product
        try {
            $catalogProduct = $this->applyProductProperties($catalogProduct, $product);
            if ($isNew) {
                // We must force price null for all store
                $stores = $this->_storeManager->getStores();
                
                $catalogProduct = $this->_productFactory->create()->load($catalogProduct->getId());
                $catalogProduct->setStatus(self::STATUS_DISABLED);
                $catalogProduct->setData('price', 0);
                
                $catalogProduct->save();
                
                foreach ($stores as $store) {
                    $catalogProduct = $this->_productFactory->create()
                        ->setStoreId($store->getId())
                        ->load($catalogProduct->getId());
                    $catalogProduct->setStoreId($store->getId());
                    $catalogProduct->setData('price', null);
                    $catalogProduct->setStatus(self::STATUS_DISABLED);
                    $catalogProduct->save();
                }
                
                $catalogProduct = $this->_productFactory->create()
                    ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->load($catalogProduct->getId());
                $catalogProduct->setData('price', null);
                $catalogProduct->setStatus(self::STATUS_DISABLED);
                $catalogProduct->save();
                //set product always offline when import from netsuite
                $catalogProduct = $this->_productFactory->create()->load($catalogProduct->getId());
                
                //ticket 967, set default product for all store
                $catalogProduct->setWebsiteIds($this->getAllWebsiteId());
                $catalogProduct->setStatus(self::STATUS_DISABLED);
                // persit catalog product data
                $catalogProduct->save();
                
                
                
            } else {
                // update product
                // set product name for default store only
                $catalogProduct->setName($product->getName())
                    ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->save();
            }
              
            
            $this->applyProductOptionByStore($catalogProduct, $product);
        } 
        catch (\Exception $ex) {
            $this->_logger->error($ex);
            throw new IcareWebApiException(500,
                __('UNEXPECTED ERROR'),
                [new IcareException('Failed to save product', $ex)]
            );
        }
        
        return
            [[
                'entity_id' => $catalogProduct->getId(),
                'sku' => $catalogProduct->getSku(),
                'name' => $catalogProduct->getName(),
                'created_at' => strtotime($catalogProduct->getCreatedAt()),
                'updated_at' => strtotime($catalogProduct->getUpdatedAt()),
            ]];
    }

    /**
     * list array website ids
     * @return array
     */
    private function getAllWebsiteId()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Store\Model\ResourceModel\Website\Collection $collection
         */
        $collection = $om->create('\Magento\Store\Model\ResourceModel\Website\Collection');
        $collection->addFieldToSelect('*');
        $sites = $collection->getData();
        $website = [];
        foreach ($sites as $site) {
            $website[] = $site['website_id'];
        }
        return $website;
    }

    /**
     * @inheritdoc
     */
    public function getBestSeller(BestSellerParamInterface $bestSellerParam)
    {
        if (empty($bestSellerParam->getStoreId())) {
            throw new ValidatorException(__('Please Input Store'));
        }
        $result = [];
        try {
            $om = ObjectManager::getInstance();
            /**@var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $collection**/
            $collection = $om->get('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
            $collection->addStoreRestrictions([$bestSellerParam->getStoreId()]);
            $collectionReport = $om->get('\Magento\Reports\Model\ResourceModel\Order\Collection');
            $dateSixMonthRange = $collectionReport->getDateRange('180d', null, null);
            $collection->setDateRange($dateSixMonthRange['from'], $dateSixMonthRange['to']);
            $products = $collection->load();
            $productIds = [];
            foreach ($products as $product) {
                $productIds[] = $product->getData('product_id');
            }

            $products = $this->_productCollection->create()
                ->setStoreId($bestSellerParam->getStoreId())
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('tax_class_id')
                ->addStoreFilter($bestSellerParam->getStoreId())
                ->addAttributeToFilter(array(array('attribute' => 'type_id',
                    'neq' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)))
                ->addAttributeToFilter(array(array('attribute' => 'status',
                    'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))
                ->addAttributeToFilter(array(array('attribute' => 'visibility',
                    'neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)));
            $products->load();
            if (empty($products)) {
                return $result;
            }

            $store = $this->_storeManager->getStore($bestSellerParam->getStoreId());
            $productData = $this->convertProducts($products, $store);
            $result[] = array('products' => $productData);

        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            throw new IntegrationException(__($ex->getMessage()));
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getNewestProduct(NewestProductParamInterface $newestProductParam)
    {
        if (empty($newestProductParam->getStoreId())) {
            throw new ValidatorException(__('Please Input Store'));
        }
        $result = [];
        try {
            $todayStartOfDayDate = $this->_localeDate
                ->date($useTimezone = false)
                ->setTime(0, 0, 0)
                ->format('Y-m-d H:i:s');
            $todayEndOfDayDate = $this->_localeDate
                ->date($useTimezone = false)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            $collection = $this->_productCollection->create();
            $collection->addAttributeToSelect('id')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('tax_class_id')
                ->addStoreFilter($newestProductParam->getStoreId())
                ->addAttributeToFilter(array(array('attribute' => 'type_id',
                    'neq' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)))
                ->addAttributeToFilter(array(array('attribute' => 'status',
                    'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))
                ->addAttributeToFilter(array(array('attribute' => 'visibility',
                    'neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)))
                ->addAttributeToFilter(
                    'news_from_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'to' => $todayEndOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left'
                )->addAttributeToFilter(
                    'news_to_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left'
                )->addAttributeToFilter(
                    [
                        ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                        ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                    ]
                )->addAttributeToSort(
                    'news_from_date',
                    'desc'
                )->setPageSize(
                    100
                )->setCurPage(
                    1
                );
            $products = $collection->load();
            if (empty($products)) {
                return $result;
            }

            $store = $this->_storeManager->getStore($newestProductParam->getStoreId());
            $productData = $this->convertProducts($products, $store);
            $result[] = array('products' => $productData);
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            throw new IntegrationException(__($ex->getMessage()));
        }
        return $result;
    }

    private function convertProducts($products, $store)
    {
        $result = array();
        $iCareHelper = ObjectManager::getInstance()->get('Icare\Custom\Helper\ICareHelper');
        foreach ($products as $product) {
            $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
            $price_after_tax = $iCareHelper->getPriceIncludeTax($product, $store);
            $tax_infos = $iCareHelper->getTaxRatePercent($product, $store);
            $item = array(
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice(),
                'tax' => $price_after_tax - $product->getPrice(),
                'price_after_tax' => $price_after_tax,
                'is_include_tax' => true,
                'image' => $imageUrl,
                'description' => $product->getDescription(),
                'tax_info' => $tax_infos
            );
            $relate = $this->getInstallmentProducts($product, $store->getId());
            if ($relate) {
                $item['installment'] = $relate;
            }
            $option = $this->getCustomOptionProducts($product);
            if ($option) {
                $item['option'] = $option;
            }
            $medias = $iCareHelper->getProductMedias($product, $store);
            $item['medias'] = $medias;
            $result[] = $item;
        }

        return $result;
    }

    private function getInstallmentProducts($product, $storeId)
    {

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $resource->getConnection();

        $select = $connection->select()->from('icare_installment_product_relation as main_table',
            ['installment.installment_id', 'installment.title',
                'installment.number_of_repayment', 'installment.description'])
            ->joinInner(['installment' => 'icare_installment_entity'],
                'main_table.installment_id = installment.installment_id',
                [])
            ->where('main_table.product_id = ?', $product->getId())
            ->where('main_table.store_id = ?', $storeId)
            ->where('installment.is_active = ?', true);
        $rows = $connection->fetchAssoc($select);
        $installments = [];
        foreach ($rows as $row) {
            $installments[] = array(
                'id' => $row['installment_id'],
                'title' => $row['title'],
                'month' => $row['number_of_repayment'],
                'description' => $row['description']
            );
        }
        return $installments;
    }

    private function getCustomOptionProducts($products)
    {
        $options = $products->getOptions();
        $renderOptions = array();
        foreach ($options as $option) {
            $renderOptionsValue = array();
            foreach ($option->getValues() as $optionType) {
                $renderOptionsValue[] = [
                    'option_type_id' => $optionType->getOptionTypeId(),
                    'value' => $optionType->getStoreTitle() ? $optionType->getStoreTitle() : $optionType->getTitle()
                ];
            }
            $renderOptions[] = [
                'option_id' => $option->getOptionId(),
                'title' => $option->getStoreTitle() ? $option->getStoreTitle() : $option->getTitle(),
                'value' => $renderOptionsValue
            ];
        }
        return $renderOptions;

    }
}