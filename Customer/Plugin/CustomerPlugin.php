<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Customer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class CustomerPlugin
{

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->customerFactory = $customerFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }
    /**
     * Plugin after create customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepository $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
//    public function beforeSave(CustomerInterface $customer)
//    {
//  //      $customer;
////        $result[] = new IcareException(__("Due Limit is not enough to place order. Please check with customer to get more due limit."));
////        throw new IcareWebApiException(402, __('Due Limit is not enough to place order. Please check with customer to get more due limit.'), $result);
//
//    }

}
