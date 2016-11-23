<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/3/2016
 * Time: 2:08 PM
 */
namespace Icare\Capture\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Capture extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
    }
    public function _construct()
    {
        $this->_init('customer_capture', 'id');
    }


    /**
     * Load capture by customerId
     */
    public function loadByCustomerId(\Icare\Capture\Model\Capture $capture, $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()
            ->from($this->getTable('customer_capture'), 'id')
            ->where('customer_id=?', $customerId);
        $Id = $connection->fetchOne($select, $bind);
        if ($Id) {
            $this->load($capture, $Id);
        } else {
            $capture->setData([]);
        }

        return $this;
    }

    /**
     * Load capture by order id
     */
    public function loadByOrderId(\Icare\Capture\Model\Capture $capture, $orderId)
    {
        $connection = $this->getConnection();
        $bind = ['order_id' => $orderId];
        $select = $connection->select()
            ->from($this->getTable('customer_capture'), 'id')
            ->where('order_id=?', $orderId);
        $Id = $connection->fetchOne($select, $bind);
        if ($Id) {
            $this->load($capture, $Id);
        } else {
            $capture->setData([]);
        }

        return $this;
    }

    /**
     * Load capture by customerId id
     */
    public function loadByDeviceId(\Icare\Capture\Model\Capture $capture, $deviceId)
    {
        $connection = $this->getConnection();
        $bind = ['cuatomer_id' => $deviceId];
        $select = $connection->select()
            ->from($this->getTable('customer_capture'), 'id')
            ->where('device_id=?', $deviceId);
        $Id = $connection->fetchOne($select, $bind);
        if ($Id) {
            $this->load($capture, $Id);
        } else {
            $capture->setData([]);
        }

        return $this;
    }
}