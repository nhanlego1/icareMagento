<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Controller\Adminhtml\Deposit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Icare_Deposit::customers_deposit');
        $resultPage->addBreadcrumb(__('Deposit Receivable'), __('Deposit Receivable'));
        $resultPage->getConfig()->getTitle()->prepend(__('Deposit Receivable'));
        return $resultPage;
    }
    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icare_Deposit::customer_deposit');
    }
}