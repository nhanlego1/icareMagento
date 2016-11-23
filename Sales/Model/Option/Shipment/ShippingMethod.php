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


class ShippingMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    protected function getOptionArray() {
        return [
            '0' => '-',
            'freeshipping_freeshipping' => __('Pickup'),
            'flatrate_flatrate' => __('Delivery')
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