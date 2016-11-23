<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Controller\Adminhtml\Deposit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;
    /**
     * Role helper
     *
     * @var \Icare\User\Helper\Role
     */
    protected $_roleHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Icare\User\Helper\Role $roleHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Icare\User\Helper\Role $roleHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->userFactory = $userFactory;
        $this->_roleHelper = $roleHelper;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $user = $this->userFactory->create()->load($userId);
        $curent_user = $this->_auth->getUser();

        if (!$this->_roleHelper->checkSpecialUser($curent_user) && $curent_user->getStoreId() != $user->getStoreId()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Icare_Deposit::customers_deposit');
        $resultPage->addBreadcrumb(__('Deposit Receivable'), __('Deposit Receivable'));
        $resultPage->getConfig()->getTitle()->prepend(__($user->getName()));

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