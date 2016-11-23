<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/22/16
 * Time: 8:47 PM
 */

namespace Icare\Sales\Block\Adminhtml\Shipment;


class ShipmentForm extends \Magento\Shipping\Block\Adminhtml\View\Form
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_adminHelper;

    /**
     * iCare address
     *
     * @var \Magento\Customer\Model\Address
     */
    protected $_icareAddress;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Customer\Model\Address $address
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Model\Address $address,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        $this->_icareAddress = $address;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $registry, $adminHelper, $carrierFactory, $data);
    }

    public function getDeliveryInfo() {
        $shipment = $this->getShipment();
        return $this->getDeliveryNote($shipment->getId());
    }

    protected function getDeliveryNote($shipmentId) {
        $resource  = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipmentId)
            ->order('attachment_id desc');
        $rows = $connection->fetchAssoc($select);
        $deliveryNotes = array();
        foreach($rows as $row) {
            if (isset($row['attachment_url'])) {
                $deliveryNotes[] = ['updated' => $row['update_time'],'is_attachment' => true, 'info' => $row['attachment_url']];
            } else if (isset($row['delivery_failed_reason'])) {
                $deliveryNotes[] = ['updated' => $row['update_time'], 'is_attachment' => false, 'info' => $row['delivery_failed_reason']];
            }
        }
        return $deliveryNotes;
    }

    protected function getDeliveryAttachment($shipmentId) {
        $resource  = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipmentId)
            ->where('attachment_url is not null');
        $rows = $connection->fetchAssoc($select);
        $attachments = array();
        foreach($rows as $row) {
            $attachments[] = $row['attachment_url'];
        }
        return $attachments;

    }

    protected function getDeliveryReason($shipmentId) {
        $resource  = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $select = $connection->select()->from('icare_shipment_attachment')
            ->where('shipment_id = ?', $shipmentId)
            ->where('delivery_failed_reason is not null');
        $rows = $connection->fetchAssoc($select);
        $reasons = array();
        foreach($rows as $row) {
            $reasons[] = $row['delivery_failed_reason'];
        }
        return $reasons;

    }

    /**
     * Rertrieve carrier name from store configuration
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($carrierCode == 'freeshipping_freeshipping') {
            return __(\Icare\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form::FREESHIPPING);
        }
        elseif ($carrierCode == 'flatrate_flatrate') {
            return __(\Icare\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form::FLATRATE);
        }

        return $carrierCode;
    }

    /**
     * Rertrieve iCare center name
     *
     * @param \Magento\Framework\DataObject $order
     * @return string
     */
    public function getIcareCenter($order)
    {
        $icareAddressId = $order->getIcareAddressId();

        // Check iCare address
        if (!empty ($icareAddressId)) {
            $iCareAddress = $this->_icareAddress->load($icareAddressId);
            return $iCareAddress;
        }

        return NULL;
    }

    /**
     * Rertrieve iCare center type
     *
     * @param \Magento\Framework\DataObject $order
     * @return string
     */
    public function getIcareType($order)
    {
        $types = [
            '1' => __('iCare Center'),
            '2' => __('Distribution Center')
        ];
        $icareAddressType = $order->getIcareAddressType();

        // Check iCare address
        if (!empty ($icareAddressType)) {
            return $types[$icareAddressType];
        }

        return '';
    }
}