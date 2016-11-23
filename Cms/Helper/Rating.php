<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 02/11/2016
 * Time: 15:38
 */

namespace Icare\Cms\Helper;

class Rating extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * Role collection
     *
     * @var \Icare\Cms\Model\ResourceModel\Page\Rating\CollectionFactory
     */
    protected $_ratingCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Icare\Cms\Model\ResourceModel\Page\Rating\CollectionFactory $ratingCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Icare\Cms\Model\ResourceModel\Page\Rating\CollectionFactory $ratingCollectionFactory
    ) {
        $this->_ratingCollectionFactory = $ratingCollectionFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param string $type
     * @param int $entityId
     * @return bool
     */
    public function getRatingInfo($type, $entityId) {
        $ratingCollection = $this->_ratingCollectionFactory->create()
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('type', $type)
            ->load();
        $rating = $ratingCollection->getItems();

        if (count($rating) > 0) {
            $rating = reset($rating);
            return $rating;
        }

        return null;
    }
}