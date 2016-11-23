<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 02/11/2016
 * Time: 10:26
 */

namespace Icare\Cms\Model\Page;
use Magento\Framework\DataObject\IdentityInterface;


class Rating extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const ID = "id";

    /**#@+
     * Post's Statuses
     */


    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'icare_page_rating';
    /**
     * @var string
     */
    protected $_cacheTag = 'icare_page_rating';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'icare_page_rating';


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Icare\Cms\Model\ResourceModel\Page\Rating');
    }


    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

}