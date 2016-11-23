<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 25/07/2016
 * Time: 15:12
 */

namespace Icare\Helpdesk\Api\Data;


interface TicketReplyInfoInterface
{

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getTicketId();

    /**
     * @param $ticketId
     * @return mixed
     */
    public function setTicketId($ticketId);

    /**
     * @return mixed
     */
    public function getAttachment();

    /**
     * @param $ticketId
     * @return mixed
     */
    public function setAttachment($attachment);


}