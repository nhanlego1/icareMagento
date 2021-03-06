<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 2:30 PM
 */

namespace Icare\Installment\Block\Adminhtml\Installment;


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
     * Initialize edit installment
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'installment_id';
        $this->_blockGroup = 'Icare_Installment';
        $this->_controller = 'adminhtml_installment';
        parent::_construct();
        if ($this->_isAllowedAction('Icare_Installment::save')) {
            $this->buttonList->update('save', 'label', __('Save iCare Installment'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
        if ($this->_isAllowedAction('Icare_Installment::installment_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Installment'));
        } else {
            $this->buttonList->remove('delete');
        }
    }
    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('icare_installment')->getId()) {
            return __("Edit Installment '%1'", $this->escapeHtml($this->_coreRegistry->registry('icare_installment')->getTitle()));
        } else {
            return __('New Installment');
        }
    }
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('icare/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

}