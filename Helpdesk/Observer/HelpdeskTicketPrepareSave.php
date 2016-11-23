<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 03/11/2016
 * Time: 16:43
 */

namespace Icare\Helpdesk\Observer;


use Magento\Framework\Event\ObserverInterface;

class HelpdeskTicketPrepareSave implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getData('ticket');
        $request = $observer->getEvent()->getData('request');
        $data = $request->getPostValue();
        /** @var \Magebuzz\Helpdesk\Model\Ticket $ticketModel */
        $ticketModel = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebuzz\Helpdesk\Model\Ticket');
        
        $ticketId = $request->getParam('ticket_id');
        $isNewTicket = false;
        if ($ticketId) {
            $ticketModel->load($ticketId);
        } else {
            $isNewTicket = true;
        }
        if ($isNewTicket) {
            if (isset($data['customer_email']) && isset($data['customer_telephone']) && isset($data['ticket_type'])) {
                $resource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
            
                $select = $connection->select()->from(
                    'customer_entity',
                    ['entity_id', 'email', 'firstname', 'lastname']
                    )->where(
                        "customer_entity.email = :email OR customer_entity.telephone = :telephone "
                        );
            
                    $binds = [
                        'email' => $data['customer_email'],
                        'telephone' => $data['customer_telephone']
                    ];
                    $customer = $connection->fetchAll($select, $binds);
                    $customer = reset($customer);
            
                    $object->setCustomerId($customer['entity_id']);
                    $object->setCustomerEmail($customer['email']);
                    $object->setCustomerName($customer['firstname'] . ' ' . $customer['lastname']);
                    $object->setTicketType($data['ticket_type']);
            }
        }
        
    }
}