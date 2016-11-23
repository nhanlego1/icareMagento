<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 25/07/2016
 * Time: 15:18
 */

namespace Icare\Helpdesk\Model;


use Icare\Helpdesk\Api\Data\TicketReplyInfoInterface;

class TicketReplyInfo implements TicketReplyInfoInterface
{

    private $_description;
    private $_ticketId;
    private $_attachment;

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function getTicketId()
    {
        return $this->_ticketId;
    }

    public function setTicketId($ticketId)
    {
        $this->_ticketId = $ticketId;
    }

    public function getAttachment()
    {
        return $this->_attachment;
    }

    public function setAttachment($attachment)
    {
        $this->_attachment = $attachment;
    }


}