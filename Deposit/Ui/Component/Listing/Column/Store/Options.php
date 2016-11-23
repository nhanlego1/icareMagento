<?php
/**
 * Created by PhpStorm.
 * User: nn
 * Date: 04/10/2016
 * Time: 14:40
 */

namespace Icare\Deposit\Ui\Component\Listing\Column\Store;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    )
    {
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->mappingArrayOption();
        }
        return $this->options;
    }
    /**
     * Mapping vaue lable for option
     */
    public function mappingArrayOption() {
        $option = [];
        $collection = $this->_storeCollectionFactory->create();
        foreach($collection as $item)
        {
            $option[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }
        return $option;
    }
}