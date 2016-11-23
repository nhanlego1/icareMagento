<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 2:19 PM
 */

namespace Icare\Shipping\Model\Shipping\Source;


class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Icare\Installment\Model\Installment
     */
    protected $shippinginstallment;
    /**
     * Constructor
     *
     * @param \Icare\Installment\Model\Installment $installment
     */
    public function __construct(\Icare\Shipping\Model\Shipping $shipping)
    {
        $this->shipping = $shipping;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->shipping->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}