<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order history tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Loan extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/loan.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }


    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Loan Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Loan Information');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/*/loan', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }



}