<?php
/**
 * Created by PhpStorm.
 * User: nn
 * Date: 01/10/2016
 * Time: 14:59
 */
namespace Icare\Deposit\Model\ResourceModel\Deposit\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;

/**
 * Class Collection
 * Collection for displaying grid of deposit
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult implements SearchResultInterface
{
    /**
     * Role helper
     *
     * @var \Icare\User\Helper\Role
     */
    protected $_roleHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Icare\User\Helper\Role $roleHelper
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Icare\User\Helper\Role $roleHelper
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        $this->_roleHelper = $roleHelper;
    }

    protected function _initSelect() {
        parent::_initSelect();
        $this->addFilterToMap('user_id', 'main_table.user_id');
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()
            ->columns([
                '(sum(amount) - (SELECT IFNULL(sum(transaction_amount), 0) FROM icare_deposit_payment WHERE user_id = main_table.user_id)) AS total_amount'
            ])
            ->joinLeft([
                'user_table' => $this->getTable('admin_user')
            ], 'main_table.user_id = user_table.user_id', [
                'user_table.email',
                'user_table.firstname',
                'user_table.lastname',
                'user_table.store_id'
            ])
            ->group('main_table.user_id');

        $user = $this->_authSession->getUser();
        $storeId = $user->getStoreId();
        if (!$this->_roleHelper->checkSpecialUser($user) && !$this->_storeManager->isSingleStoreMode()) {
            if (!is_array($storeId)) {
                $storeId = [$storeId === null ? -1 : $storeId];
            }
            if (empty($storeId)) {
                return $this;
            }
            $this->addFieldToFilter("user_table.store_id", ['in' => $storeId]);
        }
    }
    
    /**
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     *
     * @param AggregationInterface $aggregations            
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria            
     *
     * @return $this @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        if ($this->_totalRecords === null) {
            $this->_totalRecords = count($this->getItems());
        }
        return intval($this->_totalRecords);
    }

    public function getSize()
    {
        return 0;
    }

    /**
     * Set total count.
     *
     * @param int $totalCount            
     *
     * @return $this @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items            
     *
     * @return $this @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     *
     * @return DocumentInterface
     */
    public function getItems()
    {
        return $this;
    }
}