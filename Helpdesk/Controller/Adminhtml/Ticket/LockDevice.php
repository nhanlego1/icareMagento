<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 24/10/2016
 * Time: 18:54
 */

namespace Icare\Helpdesk\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action\Context;

class LockDevice extends \Magento\Backend\App\Action
{
    /**
     * Ticket factory
     *
     * @var \Magebuzz\Helpdesk\Model\TicketFactory
     */
    protected $_ticketFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Mobile security helper
     *
     * @var \Icare\MobileSecurity\Helper\ApiHelper
     */
    protected $_mobileSecurityHelper;

    /**
     * @var \Magebuzz\Helpdesk\Model\Message
     */
    protected $message;

    /**
     * @param Context $context
     * @param \Magebuzz\Helpdesk\Model\TicketFactory $ticketFactory,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param \Icare\MobileSecurity\Helper\ApiHelper $mobileSecurityHelper
     * @param \Magebuzz\Helpdesk\Model\Message $message
     */
    public function __construct(
        Context $context,
        \Magebuzz\Helpdesk\Model\TicketFactory $ticketFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Icare\MobileSecurity\Helper\ApiHelper $mobileSecurityHelper,
        \Magebuzz\Helpdesk\Model\Message $message,
        \Icare\Custom\Helper\ICareHelper $icareHelper
    ) {
        parent::__construct($context);
        $this->_ticketFactory = $ticketFactory;
        $this->_date = $date;
        $this->_mobileSecurityHelper = $mobileSecurityHelper;
        $this->message = $message;
        $this->_icareHelper = $icareHelper;
    }

    public function execute() {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticket = $this->_ticketFactory->create()->load($ticketId);
        $curent_user = $this->_auth->getUser();
        $replierName = $curent_user->getFirstname() . ' ' . $curent_user->getLastname();

        if ($this->_icareHelper->lockDevice($ticket->getCustomerId())) {
            try {
                $ticket->setStatusId(3)
                    ->setStaffId($curent_user->getId())
                    ->setStaffName($replierName)
                    ->setStaffEmail($curent_user->getEmail())
                    ->setUpdateTime($this->_date->gmtDate())
                    ->save();

                $ticketReply = __('Lock device has been successfully');
                $this->message->setTicketId($ticketId)
                    ->setMessage($ticketReply)
                    ->setReplierName($replierName)
                    ->setCreateTime($this->_date->gmtDate())
                    ->setIsStaff(true)
                    ->save();
                $this->messageManager->addSuccess(__('Lock device has been successfully.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the ticket.'));
            }
        } else {
            $this->messageManager->addError(__('Something went wrong while resetting pincode.'));
        }

        $resultRedirect->setPath('helpdesk/ticket/edit', ['ticket_id' => $ticketId]);
        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebuzz_Helpdesk::save');
    }
}