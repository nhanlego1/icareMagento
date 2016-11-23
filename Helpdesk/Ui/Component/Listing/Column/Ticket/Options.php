<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Helpdesk\Ui\Component\Listing\Column\Ticket;

use Magento\Framework\Data\OptionSourceInterface;
use Magebuzz\Helpdesk\Model\ResourceModel\Department\CollectionFactory;

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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
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
    public function mappingArrayOption(){
        $option = [];
        $items = [
            0 => __('Normal'),
            1 => ('Installment'),
            2 => __('Sales order'),
            3 => __('Credit'),
            4 => __('Security'),
            5 => __('System Support'),
            6 => __('Reset Pincode'),
            7 => __('Lock Device')
        ];
        foreach($items as $key =>$value){
            $option[] = ['value'=> $key,'label'=> $value];
        }
        return $option;
    }
}
