<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/13/16
 * Time: 10:33 AM
 */

namespace Icare\Sales\Controller\Adminhtml\Order;


use Icare\NetSuite\Helper\Payload;

class PushToNetsuite extends \Magento\Sales\Controller\Adminhtml\Order
{

    public function execute() {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();

        /**
         * @var \Psr\Log\LoggerInterface $logger
         */
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface');
        $customerReg = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Customer\Model\CustomerRegistry');

        if ($order) {
            try {
                $order = $this->orderRepository->get($order->getId());
                $payload = Payload::convertOrderPayload($order);
                $customer = $order->getCustomer();
                if (!$customer) {
                    $customer = $customerReg->retrieve($order->getCustomerId());
                }

                $payload['customer'] = Payload::convertCustomerPayload($customer);


                /**
                 * @var \Icare\NetSuite\Helper\Client\NetSuiteClient $netsuiteClient
                 */
                $netsuiteClient = \Magento\Framework\App\ObjectManager::getInstance()->get('\Icare\NetSuite\Helper\Client\NetSuiteClient');
                $logger->track($payload, ['netsuite post request']);
                $result = $netsuiteClient->postToNetSuite('OrderApi', $payload);
                $logger->track($result, ['netsuite post response']);
                $this->messageManager->addSuccess(__('Order is repushed to netsuite.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $logger->error($e);
                $this->messageManager->addError(__('Can not repush order to netsuite.'));
            }

            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}