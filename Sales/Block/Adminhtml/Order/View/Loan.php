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
class Loan extends \Magento\Backend\Block\Template
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
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_salesData = $salesData;
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

    /**
     * Get loan by order
     */
    public function getLoanData(){
        $order = $this->getOrder();
        $data = Mifos::create()->getLoan($order->getId(), $order->getLoanId());
        if($data){
            return $data->repaymentSchedule;
        }else{
            return null;
        }

    }
    /**
     * convert date from mifos
     */
    public function convertDateMifos($arrdate){
        $date = implode('-',$arrdate);
        $date = strtotime($date);
        $new_date = date('d F Y',$date);
        return $new_date;
    }

}