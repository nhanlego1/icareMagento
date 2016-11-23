<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 25/07/2016
 * Time: 15:18
 */

namespace Icare\Helpdesk\Model;


use Icare\Helpdesk\Api\Data\TicketInfoInterface;

class TicketInfo implements TicketInfoInterface
{
    private $_title;
    private $_description;
    private $_customerName;
    private $_customerEmail;
    private $_priority;
    private $_storeId;
    private $_customerId;
    private $_orderId;
    private $_ticketId;
    private $_attachment;
    private $_userId;
    private  $_ticketType;

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function getCustomerName()
    {
        return $this->_customerName;
    }

    public function setCustomerName($customerName)
    {
        $this->_customerName = $customerName;
    }

    public function getCustomerEmail()
    {
        return $this->_customerEmail;
    }

    public function setCustomerEmail($customerEmail)
    {
        $this->_customerEmail = $customerEmail;
    }

    public function getPriority()
    {
        return $this->_priority;
    }

    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    public function getCustomerId()
    {
        return $this->_customerId;
    }

    public function setCustomerId($customerId)
    {
        $this->_customerId = $customerId;
    }

    public function getOrderId()
    {
        return $this->_orderId;
    }

    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;
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

    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($userId)
    {
        $this->_userId = $userId;
    }

    public function getParams()
    {
        return isset($this->_params) ? $this->_params : '';
    }

    public function setParams($params)
    {
        $this->_params = $params;
    }
    public function getTicketType()
    {
        return $this->_ticketType;
    }

    public function setTicketType($ticketType)
    {
        $this->_ticketType = $ticketType;
    }


}