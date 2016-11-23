<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 24/10/2016
 * Time: 18:21
 */

/**
 * Ticket ticket edit form block
 */
namespace Icare\Helpdesk\Block\Adminhtml\Ticket;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'ticket_id';
        $this->_blockGroup = 'Magebuzz_Helpdesk';
        $this->_controller = 'adminhtml_ticket';
        parent::_construct();

        $ticket = $this->_coreRegistry->registry('helpdesk_ticket');
        if ($ticket->getTicketType() == 6 && $ticket->getStatusId() != 3) {
            $this->buttonList->add(
                'reset_pincode',
                [
                    'class' => 'save',
                    'label' => __('Reset Pincode'),
                    'onclick' => 'setLocation(\'' . $this->getResetPincodeUrl($ticket) . '\')'
                ],
                11
            );
        }
        if ($ticket->getTicketType() == 7 && $ticket->getStatusId() != 3) {
            $this->buttonList->add(
                'lock_device',
                [
                    'class' => 'save',
                    'label' => __('Lock Customer App'),
                    'onclick' => 'setLocation(\'' . $this->getLockDeviceUrl($ticket) . '\')'
                ],
                12
            );
        }
        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        if ($this->_coreRegistry->registry('helpdesk_ticket')->getId()) {
            $this->buttonList->remove('reset');
        }
    }

    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $ticket = $this->_coreRegistry->registry('helpdesk_ticket');
        if ($ticket->getId()) {
            return __("Edit Ticket '%1'", $this->escapeHtml($ticket->getTitle()));
        } else {
            return __('New Ticket');
        }
    }

    protected function getResetPincodeUrl($ticket) {
        return $this->getUrl('helpdesk/*/resetpincode', ['ticket_id' => $ticket->getId()]);
    }

    protected function getLockDeviceUrl($ticket){
        return $this->getUrl('helpdesk/*/lockdevice', ['ticket_id' => $ticket->getId()]);
    }
}
