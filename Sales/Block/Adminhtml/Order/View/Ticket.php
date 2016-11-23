<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\View;



/**
 * Order history block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ticket extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magebuzz\Helpdesk\Helper\Data $helpdeskHelper,
        \Magebuzz\Helpdesk\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory,
        \Magebuzz\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_salesData = $salesData;
        $this->_coreRegistry = $coreRegistry;
        $this->_helpdeskHelper = $helpdeskHelper;
        $this->_messageCollectionFactory = $messageCollectionFactory;
        $this->_ticketCollectionFactory = $ticketCollectionFactory;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;

    }


    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    public function getTicketByOrderId()
    {
        $order = $this->getOrder();
        $collection = $this->_ticketCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getId())
            ->setOrder('ticket_id', 'DESC');
        return $collection;
    }

    public function getHistoryMessages($ticketId)
    {

        $collection = $this->_messageCollectionFactory->create()
            ->addFieldToFilter('ticket_id', $ticketId)
            ->setOrder('message_id', 'DESC');

        return $collection;
    }

    /*
    * return all attached files of a message
    */
    public function getAttachments($messageId)
    {
        return $this->_helpdeskHelper->getAttachments($messageId);
    }

    public function getDownloadFileUrl($attachment)
    {
        return $this->_helpdeskHelper->getAttachmentUrl($attachment);
    }

    public function formatMessageDate($dateString)
    {
        $date = new \DateTime($dateString);
        if ($date == new \DateTime('today')) {
            return $this->_localeDate->formatDateTime(
                $date,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT
            );
        }
        return $this->_localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }
    public function getHref($ticketId)
    {
        return $this->getUrl('helpdesk/ticket/edit', ['ticket_id' => $ticketId]);
    }

}