<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 25/07/2016
 * Time: 15:12
 */

namespace Icare\Helpdesk\Api\Data;


interface TicketInfoInterface
{
    /**
     * @return mixed
     */
    public function getTitle();

    /**
     * @param $title
     * @return mixed
     */
    public function setTitle($title);

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
    public function getCustomerName();

    /**
     * @param $customerName
     * @return mixed
     */
    public function setCustomerName($customerName);

    /**
     * @return mixed
     */
    public function getCustomerEmail();

    /**
     * @param $customerEmail
     * @return mixed
     */
    public function setCustomerEmail($customerEmail);

    /**
     * @return mixed
     */
    public function getPriority();

    /**
     * @param $priority
     * @return mixed
     */
    public function setPriority($priority);

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param $storeId
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

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

    /**
     * @return mixed
     */
    public function getUserId();

    /**
     * @param $userId
     * @return mixed
     */
    public function setUserId($userId);

    /**
     * @return mixed
     */
    public function getParams();

    /**
     * @param $userId
     * @return mixed
     */
    public function setParams($params);

    /**
     * @return mixed
     */
    public function getTicketType();

    /**
     * @param $userId
     * @return mixed
     */
    public function setTicketType($ticketType);



}