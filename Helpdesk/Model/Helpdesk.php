<?php

namespace Icare\Helpdesk\Model;


use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Helpdesk\Api\HelpdeskInterface;
use Magento\Framework\App\Filesystem\DirectoryList;


class Helpdesk implements HelpdeskInterface
{

    const XML_PATH_ENABLE_EMAIL = 'helpdesk/email/enable_email_notification';
    const XML_PATH_DEFAULT_DERPARMENT = 'helpdesk/ticket/contact_ticket_department';

    /**
     *
     * @var \Icare\Cms\Helper\Rating
     */
    private $_ratingHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magebuzz\Helpdesk\Helper\Data $helpdeskHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Icare\Custom\Helper\S3Helper $s3Helper,
        \Icare\Cms\Helper\Rating $ratingHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->urlModel = $urlFactory->create();
        $this->_helpdeskHelper = $helpdeskHelper;
        $this->_date = $date;
        $this->_eventManager = $eventManager;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $scopeConfig;
        $this->_fileSystem = $fileSystem;
        $this->_s3Helper = $s3Helper;
        $this->_ratingHelper = $ratingHelper;
    }

    /**
     * @param \Icare\Helpdesk\Api\Data\TicketInfoInterface $ticketInfo
     * @return mixed
     * @throws IcareWebApiException
     */

    public function submitTicketV2(\Icare\Helpdesk\Api\Data\TicketInfoInterface $ticketInfo)
    {


        $errors = array();
        $result = array();
        if (empty($ticketInfo->getCustomerName())) {
            $result[] = new IcareException(__("Customer Name is required"));
        }

        if (empty($ticketInfo->getCustomerEmail())) {
            $result[] = new IcareException(__("Customer Email is required"));
        }

        if (empty($ticketInfo->getTitle())) {
            $result[] = new IcareException(__("Title is required"));
        }

        if (empty($ticketInfo->getDescription())) {
            $result[] = new IcareException(__("Description is required"));
        }

        if (empty($ticketInfo->getStoreId())) {
            $result[] = new IcareException(__("Store Id is required"));
        }

        if (empty($ticketInfo->getPriority())) {
            $ticketInfo->setPriority(1);
        }

        if (empty($ticketInfo->getCustomerId())) {
            $ticketInfo->setCustomerId(0);
        }

        if (empty($ticketInfo->getOrderId())) {
            $ticketInfo->setOrderId(0);
        }

        if (empty($ticketInfo->getUserId())) {
            $ticketInfo->setUserId(0);
        }
        if(empty($ticketInfo->getParams())){
            $ticketInfo->setParams('');
        }
        if(empty($ticketInfo->getTicketType())){
            $ticketInfo->setTicketType(0);
        }

        if ($errors) {
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }

        try {
            $this->_helpdeskHelper->setStoreId($ticketInfo->getStoreId());
            $storeObject = $this->_objectManager->create('\Magento\Store\Model\Store');
            $store = $storeObject->load($ticketInfo->getStoreId());
            // Get Default Department
            $departmentId = $this->_scopeConfig->getValue(
                self::XML_PATH_DEFAULT_DERPARMENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $store->getWebsiteId());

            //generate unique ticket mask id
            $data['mask_id'] = $this->_helpdeskHelper->generateTicketMaskId();
            $data['status_id'] = $this->_helpdeskHelper->getNewTicketStatus();
            $data['description'] = strip_tags($this->_helpdeskHelper->processMessage($ticketInfo->getDescription()));
            $data['stores'] = [$ticketInfo->getStoreId()];
            $data['customer_name'] = $ticketInfo->getCustomerName();
            $data['customer_email'] = $ticketInfo->getCustomerEmail();
            $data['customer_id'] = $ticketInfo->getCustomerId();
            $data['priority_id'] = $ticketInfo->getPriority();
            $data['department_id'] = 2;
            $data['order_id'] = $ticketInfo->getOrderId();
            $data['title'] = $ticketInfo->getTitle();
            $data['user_id'] = $ticketInfo->getUserId();
            $data['ticket_type'] = $ticketInfo->getTicketType();
            $data['params'] = $ticketInfo->getParams();

            $ticketModel = $this->_objectManager->create('Magebuzz\Helpdesk\Model\Ticket');
            $ticketModel->setData($data);

            //save ticket into database
            if ($ticketModel->getCreateTime() == NULL || $ticketModel->getCreateTime() == NULL) {
                $ticketModel->setCreateTime($this->_date->gmtDate())
                    ->setUpdateTime($this->_date->gmtDate());
            } else {
                $ticketModel->setUpdateTime($this->_date->gmtDate());
            }

            $this->_eventManager->dispatch(
                'helpdesk_ticket_frontend_prepare_save',
                ['ticket' => $ticketModel]
            );

            $ticketModel->save();


            //end attachment
            $currentId = $ticketModel->getId();
            $data['ticket_id'] = $currentId;
            $data['create_time'] = $this->_date->gmtTimestamp($ticketModel->getCreateTime());
            $data['update_time'] = $this->_date->gmtTimestamp($ticketModel->getUpdateTime());
            $data['status'] = $ticketModel->getStatusLabel();
            $data['params'] = $ticketModel->getParams();
            $data['ticket_type'] = $ticketModel->getTicketType();
            // save message history
            if (!empty($data['description'])) {
                $message = $this->_objectManager->create('\Magebuzz\Helpdesk\Model\Message')
                    ->setTicketId($currentId)
                    ->setMessage($data['description'])
                    ->setReplierName($ticketModel->getCustomerName())
                    ->setCreateTime($this->_date->gmtDate())
                    ->setIsPrivate(0)
                    ->setIsStaff(false)
                    ->save();
                //save attachment to ticket
                if (!empty($ticketInfo->getAttachment())) {
                    $file = $ticketInfo->getAttachment();
                    $data_image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
                    //get extension
                    $filetype = explode(';', $file);
                    $filetype = explode('/', $filetype[0]);
                    $ext = $filetype[1];
                    $filename = time() . '.' . $ext;
                    $messageId = $message->getMessageId();
                    //create folder
                    $folder = $this->_helpdeskHelper->getAttachmentFolder($messageId);
                    $files = $folder . $filename;

                    // upload to S3
                    $url = $this->_s3Helper->uploadFile($files, $data_image);

                    //save attachment ticket
                    $this->_helpdeskHelper->saveAttachFile($url, $currentId, $messageId, $files);

                }
                $data['message'] = $this->getMessageByTicket($currentId);
            }

            //send email notification
            $enableNotification = (bool)$this->_scopeConfig->getValue(
                self::XML_PATH_ENABLE_EMAIL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $ticketInfo->getStoreId());
            if ($enableNotification) {
                $ticketModel->sendEmailToCustomer();
                $ticketModel->sendEmailTicketNotification();
            }
            $result[] = $data;
            return $result;
        } catch (\Exception $ex) {
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    /**
     * @param \Icare\Helpdesk\Api\Data\TicketReplyInfoInterface $ticketReplyInfo
     * @return mixed
     * @throws IcareWebApiException
     */
    public function submitTicketReplyV2(\Icare\Helpdesk\Api\Data\TicketReplyInfoInterface $ticketReplyInfo)
    {

        $errors = array();
        $result = array();

        if (empty($ticketReplyInfo->getDescription())) {
            $result[] = new IcareException(__("Description is required"));
        }

        if (empty($ticketReplyInfo->getTicketId())) {
            $result[] = new IcareException(__("TicketId is required"));
        }

        if ($errors) {
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $errors);
        }

        try {
            $data = [];
            $currentId = $ticketReplyInfo->getTicketId();
            $ticket = $this->_objectManager->create('\Magebuzz\Helpdesk\Model\Ticket')->load($currentId);
            // save message history

            $message = $this->_objectManager->create('\Magebuzz\Helpdesk\Model\Message')
                ->setTicketId($currentId)
                ->setMessage($ticketReplyInfo->getDescription())
                ->setReplierName($ticket->getCustomerName())
                ->setCreateTime($this->_date->gmtDate())
                ->setIsPrivate(0)
                ->setIsStaff(false)
                ->save();
            //save attachment to ticket
            $data['message'] = strip_tags($message->getMessage());
            $data['message_id'] = $message->getMessageId();
            $data['replier_name'] = $message->getReplierName();
            $data['create_time'] = $this->_date->gmtTimestamp($message->getCreateTime());
            if (!empty($ticketReplyInfo->getAttachment())) {
                $file = $ticketReplyInfo->getAttachment();

                $data_image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
                //get extension
                $filetype = explode(';', $file);
                $filetype = explode('/', $filetype[0]);
                $ext = $filetype[1];
                $filename = time() . '.' . $ext;
                $messageId = $message->getMessageId();
                //add folder first
                $folder = $this->_helpdeskHelper->getAttachmentFolder($messageId);
                $files = $folder . $filename;
                // upload to S3
                $url = $this->_s3Helper->uploadFile($files, $data_image);
                //save attachment ticket
                $this->_helpdeskHelper->saveAttachFile($url, $currentId, $messageId,$files);

                $attachments = $this->_helpdeskHelper->getAttachments($messageId);
                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $data['attachments'][] = $this->_helpdeskHelper->getAttachmentUrl($attachment);
                    }

                } else {
                    $data['attachments'] = null;
                }
            }
            $result[] = $data;
            return $result;
        } catch (\Exception $ex) {
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
    }

    /**
     * @param int $customerId
     * @return $mixed
     */
    public function getListByCustomer($customerId)
    {
        // TODO: query get ticket by customer
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magebuzz\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory');
        $objectTicket = $objectManager->create('Magebuzz\Helpdesk\Model\Ticket');
        $objectMessage = $objectManager->create('Magebuzz\Helpdesk\Model\Message');
        $objectCustomer = $objectManager->create('Magento\Customer\Model\Customer');
        $objectOrder = $objectManager->create('Magento\Sales\Model\Order');
        $objectAttach = $objectManager->create('Magebuzz\Helpdesk\Model\Attachment');
        $tickets = $collection->create()
            ->addFieldToSelect('ticket_id')
            ->addFieldToSelect('mask_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('description')
            ->addFieldToSelect('department_id')
            ->addFieldToSelect('status_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('customer_name')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('staff_id')
            ->addFieldToSelect('staff_name')
            ->addFieldToSelect('staff_email')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('update_time')
            ->addFieldToSelect('params')
            ->addFieldToSelect('ticket_type')
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('update_time', 'desc');

        $tickets->load();
        $data = [];
        foreach ($tickets as $tick) {
            $ticket = $objectTicket->loadByMaskId($tick->getMaskId());
            $ticketData = $ticket->getData();
            $ticketData['update_time'] = strtotime($ticketData['update_time']);
            $ticketData['create_time'] = strtotime($ticketData['create_time']);
            $ticketData['description'] = $ticketData['description'];
            //TODO: Get message
            $ticketData['message'] = $this->getMessageByTicket($ticketData['ticket_id']);
            //TODO: get customer info
            $customer = $objectCustomer->load($ticketData['customer_id']);
            $address = $customer->getAddresses();
            $address = reset($address);
            if ($address) {
                $ticketData['customer_telephone'] = $address->getTelephone();
            } else {
                $ticketData['customer_telephone'] = null;
            }
            //TODO: Get status ticket
            $ticketData['status'] = $ticket->getStatusLabel();
            //TODO: Get order info
            if ($ticketData['order_id'] > 0) {
                $order = $objectOrder->load($ticketData['order_id']);
                $ticketData['increment_order_id'] = $order->getIncrementId();
            } else {
                $ticketData['increment_order_id'] = null;
            }
            //full infor for ticket
            $data[] = $ticketData;
        }
        return $data;
    }

    /**
     * @param int $orderId
     * @return $mixed
     */
    public function getListByOrder($orderId)
    {
        // TODO: query get ticket by order
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magebuzz\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory');
        $objectTicket = $objectManager->create('Magebuzz\Helpdesk\Model\Ticket');
        $objectMessage = $objectManager->create('Magebuzz\Helpdesk\Model\Message');
        $objectCustomer = $objectManager->create('Magento\Customer\Model\Customer');
        $objectOrder = $objectManager->create('Magento\Sales\Model\Order');
        $objectAttach = $objectManager->create('Magebuzz\Helpdesk\Model\Attachment');
        $tickets = $collection->create()
            ->addFieldToSelect('ticket_id')
            ->addFieldToSelect('mask_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('description')
            ->addFieldToSelect('department_id')
            ->addFieldToSelect('status_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('customer_name')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('staff_id')
            ->addFieldToSelect('staff_name')
            ->addFieldToSelect('staff_email')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('update_time')
            ->addFieldToSelect('params')
            ->addFieldToSelect('ticket_type')
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('update_time', 'desc');

        $tickets->load();
        $data = [];
        foreach ($tickets as $tick) {
            $ticket = $objectTicket->loadByMaskId($tick->getMaskId());
            $ticketData = $ticket->getData();
            $ticketData['update_time'] = strtotime($ticketData['update_time']);
            $ticketData['create_time'] = strtotime($ticketData['create_time']);
            $ticketData['description'] = $ticketData['description'];
            //TODO: Get message
            $ticketData['message'] = $this->getMessageByTicket($ticketData['ticket_id']);
            //TODO: get customer info
            $customer = $objectCustomer->load($ticketData['customer_id']);
            $address = $customer->getAddresses();
            $address = reset($address);
            if ($address) {
                $ticketData['customer_telephone'] = $address->getTelephone();
            } else {
                $ticketData['customer_telephone'] = null;
            }
            //TODO: Get status ticket
            $ticketData['status'] = $ticket->getStatusLabel();
            //TODO: Get order info
            if ($ticketData['order_id'] > 0) {
                $order = $objectOrder->load($ticketData['order_id']);
                $ticketData['increment_order_id'] = $order->getIncrementId();
            } else {
                $ticketData['increment_order_id'] = null;
            }
            //full infor for ticket
            $data[] = $ticketData;
        }
        return $data;
    }

    /**
     * @param int $userId
     * @return $mixed
     */
    public function getListByUser($userId)
    {
        // TODO: query get ticket by order
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magebuzz\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory');
        $objectTicket = $objectManager->create('Magebuzz\Helpdesk\Model\Ticket');
        $objectMessage = $objectManager->create('Magebuzz\Helpdesk\Model\Message');
        $objectCustomer = $objectManager->create('Magento\Customer\Model\Customer');
        $objectOrder = $objectManager->create('Magento\Sales\Model\Order');
        $objectAttach = $objectManager->create('Magebuzz\Helpdesk\Model\Attachment');
        $tickets = $collection->create()
            ->addFieldToSelect('ticket_id')
            ->addFieldToSelect('mask_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('description')
            ->addFieldToSelect('department_id')
            ->addFieldToSelect('status_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('customer_name')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect('staff_id')
            ->addFieldToSelect('staff_name')
            ->addFieldToSelect('staff_email')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('update_time')
            ->addFieldToSelect('params')
            ->addFieldToSelect('ticket_type')
            ->addFieldToFilter('user_id', $userId)
            ->setOrder('update_time', 'desc');

        $tickets->load();
        $data = [];
        foreach ($tickets as $tick) {
            $ticket = $objectTicket->loadByMaskId($tick->getMaskId());
            $ticketData = $ticket->getData();
            $ticketData['update_time'] = strtotime($ticketData['update_time']);
            $ticketData['create_time'] = strtotime($ticketData['create_time']);
            $ticketData['description'] = $ticketData['description'];
            //TODO: Get message
            $ticketData['message'] = $this->getMessageByTicket($ticketData['ticket_id']);
            //TODO: get customer info
            $customer = $objectCustomer->load($ticketData['customer_id']);
            $address = $customer->getAddresses();
            $address = reset($address);
            if ($address) {
                $ticketData['customer_telephone'] = $address->getTelephone();
            } else {
                $ticketData['customer_telephone'] = null;
            }

            //TODO: Get status ticket
            $ticketData['status'] = $ticket->getStatusLabel();
            //TODO: Get order info
            if ($ticketData['order_id'] > 0) {
                $order = $objectOrder->load($ticketData['order_id']);
                $ticketData['increment_order_id'] = $order->getIncrementId();
            } else {
                $ticketData['increment_order_id'] = null;
            }
            //full infor for ticket
            $data[] = $ticketData;
        }
        return $data;
    }

    /**
     * @param int $ticket_id
     * Get ticket by customer
     * @return $mixed
     */
    public function getMessageByTicket($ticket_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magebuzz\Helpdesk\Model\ResourceModel\Message\CollectionFactory');
        $objectMessage = $objectManager->create('Magebuzz\Helpdesk\Model\Message');;
        $messages = $collection->create()
            ->addFieldToSelect('message_id')
            ->addFieldToSelect('message')
            ->addFieldToSelect('replier_name')
            ->addFieldToSelect('create_time')
            ->addFieldToSelect('is_staff')
            ->addFieldToFilter('ticket_id', $ticket_id)
            ->setOrder('create_time', 'ASC');

        $messages->load();
        $messageData = [];
        foreach ($messages as $mas) {
            $message = $objectMessage->load($mas->getMessageId());
            $data = $message->getData();
            $data['create_time'] = strtotime($data['create_time']);
            $data['message'] = $data['message'];
            $attachments = $this->_helpdeskHelper->getAttachments($data['message_id']);
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $data['attachments'][] = $this->_helpdeskHelper->getAttachmentUrl($attachment);
                }
            } else {
                $data['attachments'] = null;
            }
            $messageData[] = $data;
        }
        return $messageData;
    }

    /**
     * @api
     * @param int $ticketId
     * @return mixed
     */
    public function getTicket($ticketId)
    {
        //TODO Loadticket by ticket ID
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectTicket = $objectManager->create('Magebuzz\Helpdesk\Model\Ticket');
        $objectMessage = $objectManager->create('Magebuzz\Helpdesk\Model\Message');
        $objectCustomer = $objectManager->create('Magento\Customer\Model\Customer');
        $objectOrder = $objectManager->create('Magento\Sales\Model\Order');
        $objectAttach = $objectManager->create('Magebuzz\Helpdesk\Model\Attachment');
        $ratingCollectionFactory = $objectManager->get('\Icare\Cms\Model\ResourceModel\Page\Rating\CollectionFactory');

        $ticket = $objectTicket->load($ticketId);
        $ticketData = $ticket->getData();
        $ticketData['update_time'] = strtotime($ticketData['update_time']);
        $ticketData['create_time'] = strtotime($ticketData['create_time']);
        //TODO: Get message
        $ticketData['message'] = $this->getMessageByTicket($ticketData['ticket_id']);
        //TODO: get customer info
        $customer = $objectCustomer->load($ticketData['customer_id']);
        $address = $customer->getAddresses();
        $address = reset($address);
        if ($address) {
            $ticketData['customer_telephone'] = $address->getTelephone();
        } else {
            $ticketData['customer_telephone'] = null;
        }

        //TODO: Get status ticket
        $ticketData['status'] = $ticket->getStatusLabel();
        //TODO: Get order info
        if ($ticketData['order_id'] > 0) {
            $order = $objectOrder->load($ticketData['order_id']);
            $ticketData['increment_order_id'] = $order->getIncrementId();
        } else {
            $ticketData['increment_order_id'] = null;
        }

        $rating = $this->_ratingHelper->getRatingInfo('ticket', $ticketId);
        if ($rating) {
            $ticketData['rating_info']['id'] = $rating->getId();
            $ticketData['rating_info']['page_id'] = $rating->getPageId();
            $ticketData['rating_info']['customer_id'] = $rating->getCustomerId();
            $ticketData['rating_info']['rating'] = $rating->getRating();
            $ticketData['rating_info']['data'] = $rating->getData('data');
            $ticketData['rating_info']['creation_time'] = strtotime($rating->getCreationTime());
        }
        //full infor for ticket
        $data[] = $ticketData;
        return $data;
    }


}