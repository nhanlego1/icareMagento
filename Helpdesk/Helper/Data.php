<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/19/16
 * Time: 9:56 PM
 */

namespace Icare\Helpdesk\Helper;


class Data extends \Magebuzz\Helpdesk\Helper\Data
{
    public function __construct(
        \Icare\Custom\Helper\S3Helper $s3Helper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Quickresponse\CollectionFactory $quickresponseCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory,
        \Magebuzz\Helpdesk\Model\DepartmentFactory $departmentFactory,
        \Magebuzz\Helpdesk\Model\StatusFactory $statusFactory,
        \Magebuzz\Helpdesk\Model\PriorityFactory $priorityFactory,
        \Magebuzz\Helpdesk\Model\QuickresponseFactory $quickresponseFactory,
        \Magebuzz\Helpdesk\Model\TicketFactory $ticketFactory,
        \Magebuzz\Helpdesk\Model\AttachmentFactory $attachmentFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\User\Model\UserFactory $userFactory)
    {
        $this->_s3Helper = $s3Helper;
        parent::__construct($context, $resource, $storeManager, $coreRegistry, $customerSession, $jsonEncoder, $resourceHelper, $uploaderFactory, $fileSystem, $localeDate, $roleCollectionFactory, $orderCollectionFactory, $orderFactory, $departmentCollectionFactory, $statusCollectionFactory, $priorityCollectionFactory, $quickresponseCollectionFactory, $ticketCollectionFactory, $messageCollectionFactory, $departmentFactory, $statusFactory, $priorityFactory, $quickresponseFactory, $ticketFactory, $attachmentFactory, $attachmentCollectionFactory, $customerFactory, $userFactory);
    }

    public function getAttachmentFolder($messageId)
    {
        if ($this->_s3Helper->isS3Usage()) {
            return '/magebuzz/ticket/message/' . $messageId;
        } else {
            return parent::getAttachmentFolder($messageId);
        }
    }

    public function uploadAttachment($file, $ticketId, $messageId) {
        if ($this->_s3Helper->isS3Usage()) {
            $key = $this->getAttachmentFolder($messageId);
            if (is_uploaded_file($file['tmp_name'])) {
                if(isset($file['name']) && $file['name'] != '') {
                    $url = $this->_s3Helper->uploadFile($key . '/' . $file['name'], fopen($file['tmp_name'], 'rb'));
                    unlink($file['tmp_name']);
                    $this->saveAttachFile($url, $ticketId, $messageId, $key . '/' . $file['name']);
                    return;
                }

            }
        } else {
            return parent::uploadAttachment($file, $ticketId, $messageId);
        }
    }

    /*
    * Attachment URL
    */
    public function getAttachmentUrl($attachment)
    {

        if ($this->_s3Helper->isS3Usage() && strpos($attachment->getFilename(), 'http') !== false) {
            return $attachment->getFilename();
        } else {
            return parent::getAttachmentUrl($attachment);
        }
    }

    /*
    * save attached file
    */
    public function saveAttachFile($filename, $ticketId, $messageId, $s3_key = null)
    {
        $model = $this->_attachmentFactory->create();
        $model->setTicketId($ticketId)
            ->setMessageId($messageId)
            ->setFilename($filename);
        $model->setData('s3_key', $s3_key);
        try {
            $model->save();
        }
        catch (Exception $ex) {
            //silence is gold
        }

        return;
    }

    public function getSuggestedCustomersJson($namePart)
    {
        $table = $this->_resource->getTableName('customer_entity');
        $connection = $this->_resource->getConnection();

        $select = $connection->select()->from(
            $table,
            ['entity_id', 'telephone', 'firstname', 'lastname']
        )->where(
            "{$table}.telephone LIKE :name_part"
        );

        $binds = ['name_part' => '%'.$namePart.'%'];
        $customers = $connection->fetchAll($select, $binds);

        $matchs = [];

        foreach ($customers as $customer) {
            $matchs[] = [
                'id' => $customer['entity_id'],
                'label' => $customer['telephone'],
                'path' => $customer['firstname'] . ' ' . $customer['lastname']
            ];
        }

        return $this->_jsonEncoder->encode($matchs);
    }

}