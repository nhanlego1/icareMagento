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

namespace Icare\Installment\Model\Installment\Source;


class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Icare\Installment\Model\Installment
     */
    protected $installment;
    /**
     * Constructor
     *
     * @param \Icare\Installment\Model\Installment $installment
     */
    public function __construct(\Icare\Installment\Model\Installment $installment)
    {
        $this->installment = $installment;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->installment->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}