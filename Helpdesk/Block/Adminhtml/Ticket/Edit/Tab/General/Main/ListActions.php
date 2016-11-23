<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 26/10/2016
 * Time: 16:16
 */

namespace Icare\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\General\Main;

/**
 * Ticket inventory data
 */
class ListActions extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'ticket/tab/general/list_actions.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    protected $_helpdeskHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magebuzz\Helpdesk\Helper\Data $helpdeskHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_authSession = $authSession;
        $this->_helpdeskHelper = $helpdeskHelper;
        parent::__construct($context, $data);
    }

    public function getTicket()
    {
        if (!$this->getData('helpdesk_ticket') instanceof \Magebuzz\Helpdesk\Model\Ticket) {
            $this->setData('helpdesk_ticket', $this->_coreRegistry->registry('helpdesk_ticket'));
        }
        return $this->getData('helpdesk_ticket');
    }

    /**
     * @return string
     */
    public function _getBlockCustomerUrl()
    {
        return $this->getUrl(
            'helpdesk/ticket/blockCustomer',
            [
                '_current' => true,
                'back' => true,
                'active_tab' => '',
                'ticket_id' => $this->getTicket()->getId()
            ]
        );
    }

    /**
     * @return string
     */
    public function _getWatchTicketUrl()
    {
        return $this->getUrl(
            'helpdesk/ticket/watchTicket',
            [
                '_current' => true,
                'back' => true,
                'active_tab' => '',
                'ticket_id' => $this->getTicket()->getId()
            ]
        );
    }

    /**
     * @return string
     */
    public function _getWatchTicketLabel()
    {
        return $this->_isWatchedTicket() ? __('Stop Watching This Ticket')
            : __('Watch This Ticket');
    }

    /**
     * @return string
     */
    protected function _isWatchedTicket()
    {
        $user = $this->_authSession->getUser();
        $ticketWatchers = $this->getTicket()->getWatchers();

        return in_array($user->getEmail(), $ticketWatchers);
    }
}
