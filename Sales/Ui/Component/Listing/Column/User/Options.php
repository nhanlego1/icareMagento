<?php
/**
 * Created by PhpStorm.
 * User: nn
 * Date: 04/10/2016
 * Time: 14:40
 */

namespace Icare\Sales\Ui\Component\Listing\Column\User;

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
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $_userCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    )
    {
        $this->_userCollectionFactory = $userCollectionFactory;
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
        $collection = $this->_userCollectionFactory->create();
        foreach($collection as $item)
        {
            $option[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }
        return $option;

    }
}