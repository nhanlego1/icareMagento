<?php
/**
 * Created by PhpStorm.
 * User: nn
 * Date: 01/10/2016
 * Time: 14:59
 */

namespace Icare\Deposit\Model\ResourceModel\Deposit\User\Grid;

/**
 * Class Collection
 * Collection for displaying grid of deposit by user
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * List deposit by user
     * @return $this
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()
            ->joinLeft(
                ['customer_table' => $this->getTable('customer_grid_flat')],
                'main_table.customer_id = customer_table.entity_id',
                ['customer_table.name', 'customer_table.social_id', 'customer_table.telephone']);
    }

}