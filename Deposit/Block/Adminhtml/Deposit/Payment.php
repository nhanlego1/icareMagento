<?php

/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 05/10/2016
 * Time: 11:37
 */
namespace Icare\Deposit\Block\Adminhtml\Deposit;
use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Payment extends \Magento\Backend\Block\Widget{
     public $_template = 'view/payment.phtml';
     public $registry;
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;
    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $registry,
        \Magento\User\Model\UserFactory $userFactory,
        PriceCurrencyInterface $priceFormatter
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->userFactory = $userFactory;
        $this->priceFormatter = $priceFormatter;
    }

    public function getPaymentItem(){
        return $this->registry->registry('payment_detail_item');
    }

    public function formatAmount($amount, $user_id) {
           $user = $this->userFactory->create()->load($user_id);
           $storeId = $user->getStoreId();
           $store = $this->_storeManager->getStore($storeId);
           $currencyCode = $store->getCurrentCurrency()->getCode();
           return $this->priceFormatter->format($amount, false, null, null, $currencyCode);
    }
}