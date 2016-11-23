<?php
namespace Icare\Customer\Model;

use Icare\Customer\Api\WishlistManagementInterface;
use Icare\Exception\Model\IcareWebApiException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerManagement;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Defines the implementaiton class of the WishlistManagementInterface
 */
class WishlistManagement implements WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;
    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Item
     */
    protected $_itemFactory;
    
    /**
     * 
     * @var $_logger
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    public $_customerRepository;
    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Framework\App\Helper\Context $context,
        CustomerRepository $customerRepository
        ) {
            $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
            $this->_wishlistRepository = $wishlistRepository;
            $this->_productRepository = $productRepository;
            $this->_wishlistFactory = $wishlistFactory;
            $this->_itemFactory = $itemFactory;
            $this->_customerRepository = $customerRepository;
            $this->_logger = $context->getLogger();
            $this->_logger->setClass($this);
    }

    /**
     * Get wishlist collection
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {

        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new IcareWebApiException(500, __('Id required'));
        } else {
            try {
                $collection =
                $this->_wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId);
                
                $wishlistData = [];
                foreach ($collection as $item) {
                    $productInfo = $item->getProduct()->toArray();
                    $productInfo['created_at'] = strtotime($productInfo['created_at']);
                    $productInfo['updated_at'] = strtotime($productInfo['updated_at']);
                    $data = [
                        "wishlist_item_id" => $item->getWishlistItemId(),
                        "wishlist_id"      => $item->getWishlistId(),
                        "product_id"       => $item->getProductId(),
                        "store_id"         => $item->getStoreId(),
                        "added_at"         => strtotime($item->getAddedAt()),
                        "description"      => $item->getDescription(),
                        "qty"              => round($item->getQty()),
                        "product"          => $productInfo
                    ];
                    $wishlistData[] = $data;
                }
                return $wishlistData;
            } catch (\Exception $e) {
                $this->_logger->error($e);
                throw new IcareWebApiException(500, __('Can not get wishlist items'));
            }
            
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        $this->_logger->info(sprintf('addWishlistForCustomer[customerId=%s,productId=%s]', $customerId, $productId));
        if ($productId == null) {
            throw new IcareWebApiException(404,__
                ('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->_productRepository->getById($productId);

        } catch (NoSuchEntityException $e) {
            throw new IcareWebApiException(404,__
                ('Invalid product, Please select a valid product'));
        }
        try {
            $customer = $this->_customerRepository->getById($customerId);;

        } catch (NoSuchEntityException $e) {
            throw new IcareWebApiException(404,__
                ('Invalid customer, Please select a valid customer'));
        }
        $product->setStoreId($customer->getStoreId());
        $result = array();
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $item = $wishlist->addNewItem($product);
            $wishlist->save();
            $result[] = [
                'customerId' => $customerId,
                'productId' => $productId,
                'result' => true
            ];
        } catch (\Exception $e) {
            $this->_logger->error($e);
            throw new IcareWebApiException(500, __('Can not add wishlist'));
        }
        return $result;
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return bool|\Magento\Wishlist\Api\status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {
       $this->_logger->info(sprinf('Delete WishList[CustomerId=%s, wishlistId=%s]', $customerId, $wishlistItemId));
        if ($wishlistItemId == null) {
            throw new IcareWebApiException(404, __
                ('Invalid wishlist item, Please select a valid item'));
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            throw new IcareWebApiException(404,
                __('The requested Wish List Item doesn\'t exist.')
                );
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            throw new IcareWebApiException(404,
                __('The requested Wish List doesn\'t exist.')
                );
        }
        if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
            throw new IcareWebApiException(404, 
                __('The requested Wish List doesn\'t exist.')
                );
        }
        $result = array();
        try {
            $item->delete();
            $wishlist->save();
            $result[] = ['result' => true];
        } catch (\Exception $e) {
            $this->_logger->error($e);
            throw new IcareWebApiException(500, __('Can not delete wishlist'));
        }
        return $result;
    }

    /**
     * Return count of wishlist item for customer
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWishlistInfo($customerId){

        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new IcareWebApiException(500, __('Id required'));
        } else {
            $collection =
            $this->_wishlistCollectionFactory->create()
            ->addCustomerIdFilter($customerId);

            $totalItems = count($collection);

            $data = [
                "total_items"      => $totalItems
            ];

            $wishlistData[] = $data;

            return $wishlistData;
        }
    }
}