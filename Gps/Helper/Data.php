<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */
namespace Icare\Installment\Helper;

/**
 * Installment data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\ObjectManager $_objectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection $_resource
     */
    protected $_resource;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        parent::__construct($context);
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array
     */
    public function getInstallmentProduct($productId, $storeId) {
        $connection = $this->_resource->getConnection();

        $select = $connection->select()->from('icare_installment_product_relation as main_table',
            ['installment.installment_id', 'installment.title',
                'installment.number_of_repayment', 'installment.description'])
            ->joinInner(['installment' => 'icare_installment_entity'],
                'main_table.installment_id = installment.installment_id',
                [])
            ->where('main_table.product_id = ?', $productId)
            ->where('main_table.store_id = ?', $storeId)
            ->where('installment.is_active = ?', true);
        $rows = $connection->fetchAssoc($select);
        $installments = [];
        foreach ($rows as $row) {
            $installments[] = array(
                'id' =>$row['installment_id'],
                'title' => $row['title'],
                'number_of_repayment' => $row['number_of_repayment'],
                'description' => $row['description']
            );
        }
        return $installments;
    }

    /**
     * @param $orderItem
     * @return array
     */
    public function getInstallmentByOrder($orderItem) {
        $orderItemObj = $this->_objectManager->get('\Magento\Sales\Model\Order\Item');
        $orderItem = $orderItemObj->load($orderItem->getId());
        $installmentInfo = $orderItem->getInstallmentInformation();
        $installments = [];
        if (isset($installmentInfo)) {
            $temp = json_decode($installmentInfo, true);
            if (isset($temp['number_of_repayment'])) {
                $temp['month'] = $temp['number_of_repayment'];
                unset($temp['number_of_repayment']);
            }
            $installments[] = $temp;
        }
        return $installments;
    }


}
