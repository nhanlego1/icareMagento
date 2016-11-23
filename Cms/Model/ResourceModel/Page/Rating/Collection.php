<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 02/11/2016
 * Time: 11:19
 */

namespace Icare\Cms\Model\ResourceModel\Page\Rating;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Construct
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init(
            'Icare\Cms\Model\Page\Rating',
            'Icare\Cms\Model\ResourceModel\Page\Rating'
        );
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource
        );
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $id
     * @param boolean $exclude
     * @return $this
     */
    public function addIdFilter($id, $exclude = false)
    {
        if (empty($id)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($id)) {
            if (!empty($id)) {
                if ($exclude) {
                    $condition = ['nin' => $id];
                } else {
                    $condition = ['in' => $id];
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = ['neq' => $id];
            } else {
                $condition = $id;
            }
        }
        $this->addFieldToFilter('id', $condition);
        return $this;
    }
}