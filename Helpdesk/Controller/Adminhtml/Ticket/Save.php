<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 03/11/2016
 * Time: 14:51
 */

namespace Icare\Helpdesk\Controller\Adminhtml\Ticket;

class Save extends \Magebuzz\Helpdesk\Controller\Adminhtml\Ticket\Save
{
    public function execute() {
        $data = $this->_request->getPostValue();

        if (!$data['customer_email'] && !$data['customer_telephone']) {
            $this->messageManager->addError(__('Customer must input email or phone'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('helpdesk/ticket/new');
        }

        return parent::execute();
    }
}