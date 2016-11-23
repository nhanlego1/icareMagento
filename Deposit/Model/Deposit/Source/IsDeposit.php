<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */


namespace Icare\Deposit\Model\Deposit\Source;


class IsDeposit implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Icare\Deposit\Model\Deposit
     */
    protected $deposit;
    /**
     * Constructor
     *
     * @param \Icare\Deposit\Model\Deposit $deposit
     */
    public function __construct(\Icare\Deposit\Model\Deposit $deposit)
    {
        $this->deposit = $deposit;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->deposit->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}