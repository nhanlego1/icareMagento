<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 05/10/2016
 * Time: 11:30
 */

namespace Icare\Deposit\Controller\Adminhtml\Deposit;


use Icare\Exception\Model\IcareException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Event\Magento;

class Payment extends \Magento\Backend\App\Action{


    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $registry;
    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * @return
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if($id){
            $om = ObjectManager::getInstance();
            /**@var \Icare\Deposit\Model\Payment $payment**/
            $payment = $om->get('Icare\Deposit\Model\Payment');
            $payment->load($id);
            if(empty($payment))
                throw  new IcareException("No payment found");
            $resultPage = $this->resultPageFactory->create();

            $user = $om->get('Magento\User\Model\User')->load($payment->getData('created_by'));
            $payment->setData('user',$user);

            $this->registry->register('payment_detail_item',$payment);
            $block = $resultPage->getLayout()
                ->createBlock('Icare\Deposit\Block\Adminhtml\Deposit\Payment','payment_detail',['payment' => $payment])
                ->toHtml('payment_detail');
            $this->getResponse()->setBody($block);
        }

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