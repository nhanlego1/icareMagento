<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\View;


use Icare\Mifos\Helper\Mifos;
/**
 * Order history block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Capture extends \Magento\Backend\Block\Template
{
    const API_KEY = 'google_api_key';
    const CANCELED = 'canceled';
    const PENDING = 'pending';
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
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_salesData = $salesData;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
        $this->_variables = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Variable\Model\Variable');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
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

    /**
     * Get capture info
     * @return mixed
     */

    public function getCapture(){
        $order = $this->getOrder();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $capture = $om->create('\Icare\Capture\Model\Capture')->loadByOrderId($order->getId());
        return $capture->getData();
    }

    /**
     * get variable key api
     */
    public function getGoogleApiKey(){
        $key = $this->_variables->setStoreId($this->getOrder()->getStoreId())->loadByCode(self::API_KEY)->getPlainValue();
        return $key;
    }
    /**
     * Get array status
     */
    public function arrStatus(){
        return array(self::CANCELED,self::PENDING);
    }

}
