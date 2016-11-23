<?php
/**
 * Created by PhpStorm.
 * User: baonq, Nhanlq
 * Date: 25/07/2016
 * Time: 15:11
 */

namespace Icare\Helpdesk\Api;


interface HelpdeskInterface
{
    /**
     * @param Data\TicketInfoInterface $ticketInfo
     * @return mixed
     */
    public function submitTicketV2(\Icare\Helpdesk\Api\Data\TicketInfoInterface $ticketInfo);

    /**
     * @param Data\TicketReplyInfoInterface $ticketReplyInfo
     * @return mixed
     */
    public function submitTicketReplyV2(\Icare\Helpdesk\Api\Data\TicketReplyInfoInterface $ticketReplyInfo);


    /**
     * @api
     * @param int $customerId
     * @return mixed
     */
    public function getListByCustomer($customerId);

    /**
     * @api
     * @param int $orderId
     * @return mixed
     */
    public function getListByOrder($orderId);

    /**
     * @api
     * @param int $userId
     * @return mixed
     */
    public function getListByUser($userId);

    /**
     * @api
     * @param int $ticketId
     * @return mixed
     */
    public function getTicket($ticketId);

}
