<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/20/16
 * Time: 9:49 AM
 */

namespace Icare\Helpdesk\Observer;


use Magento\Framework\Event\ObserverInterface;

class HelpdeskTicketBeforeDelete implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ticket = $observer->getEvent()->getData('data_object');
        if ($ticket) {
            $ticketId = $ticket->getId();
            $s3Helper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Icare\Custom\Helper\S3Helper');
            $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select = $connection->select()->from('mb_ticket_attachment')
                ->where('ticket_id = ?', $ticketId);
            $rows = $connection->fetchAssoc($select);
            foreach ($rows as $row) {
                if ($row['s3_key']) {
                    $s3Helper->deleteFile($row['s3_key']);
                }
            }
        }
    }
}