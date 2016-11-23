<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\General;

class Left extends \Magebuzz\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\General\Left
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    protected $_coreRegistry;

    protected $_formFactory;

    protected $_helpdeskHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magebuzz\Helpdesk\Helper\Data $heldeskHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
        $this->_formFactory = $formFactory;
        $this->_coreRegistry = $registry;
        $this->_helpdeskHelper = $heldeskHelper;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $registry, $formFactory, $heldeskHelper, $jsonEncoder, $data);
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $tk = parent::_prepareForm();
        $form = parent::getForm();
        $ticket = $this->getTicket();
        $fieldset = $form->addFieldset('ticket_type_fieldset', ['legend' => '']);
        $fieldset->addField(
            'ticket_type',
            'select',
            [
                'label' => __('Ticket Type'),
                'title' => __('Ticket Type'),
                'name' => 'ticket_type',
                'required' => false,
                'options' => [
                    0 => __('Normal'),
                    1 => ('Installment'),
                    2 => __('Sales order'),
                    3 => __('Credit'),
                    4 => __('Security'),
                    5 => __('System Support'),
                    6 => __('Reset Pincode'),
                    7 => __('Lock Device')
                ]
            ]
        );

        $emailField = $form->getElement('customer_email');
        $emailField->setRequired(false);
        $nameField = $form->getElement('customer_name');
        $nameField->setReadonly(true, false);
        $baseFieldset = $form->getElement('base_fieldset');

        $baseFieldset->addField(
            'customer_telephone',
            'text',
            [
                'name' => 'customer_telephone',
                'title' => __('Telephone'),
                'placeholder' => __('type telephone...'),
                'after_element_html' => $this->getAfterElementHtml()
            ]
        );

        $form->setValues($ticket->getData());
        $this->setForm($form);
        return $tk;
    }

    public function getTicket()
    {
        if (!$this->getData('helpdesk_ticket') instanceof \Magebuzz\Helpdesk\Model\Ticket) {
            $this->setData('helpdesk_ticket', $this->_coreRegistry->registry('helpdesk_ticket'));
        }
        return $this->getData('helpdesk_ticket');
    }

    /**
     *
     * @return string
     */
    protected function getAfterElementHtml()
    {
        $selectorOptions = $this->_jsonEncoder->encode($this->getSelectorOptions());

        return <<<HTML
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#ticket_customer_telephone').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function getSelectorOptions()
    {
        return [
            'source' => $this->getUrl('helpdesk/ticket/suggestcustomer'),
            'valueField' => '#customer_telephone',
            'className' => 'customer-email-select',
            'multiselect' => false,
            'showAll' => false
        ];
    }

}