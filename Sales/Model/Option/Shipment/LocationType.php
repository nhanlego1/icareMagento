<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/5/16
 * Time: 11:38 AM
 */

namespace Icare\Sales\Model\Option\Shipment;


class LocationType implements \Magento\Framework\Data\OptionSourceInterface
{
    protected function getOptionArray() {
        return [
            '0' => '-',
            '1' => __('iCare Center'),
            '2' => __('Distribution Center')
        ];
    }
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->getOptionArray() as $key => $name) {
            $options[] = array(
                'value' => $key,
                'label' => $name,
            );
        }
        return $options;
    }
}