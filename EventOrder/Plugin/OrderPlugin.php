<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 7/20/2016
 * Time: 9:49 AM
 */

namespace Icare\EventOrder\Plugin;

use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Mifos\Helper\Mifos;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Icare\Custom\Helper\Custom;

class OrderPlugin
{

    private $messageManager;
    const ORDER_PENDING = 'pending';
    const ORDER_SHIPMENT = 'order_shipment';
    const ORDER_CANCEL = 'canceled';
    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';
    const CREDIT_LIMIT = 'credit_limit';
    const DUE_LIMIT = 'due_limit';
    const ORDER_PACKED = 'packed';
    const ORDER_CONFIRMED = 'confirmed';
    const ORDER_SHIPPED = 'shipped';
    const ORDER_DELIVERED = 'delivered';
    const ORDER_DELIVERY_FAILED = 'delivery_failed';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Helper\Context $contextHelper,
        AdminSession $adminSession,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
    )
    {
        $this->_adminSession = $adminSession;
        $this->messageManager = $context->getMessageManager();
        $this->_historyFactory = $historyFactory;
        $this->_logger = $contextHelper->getLogger();
        $this->_logger->setClass($this);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function beforeSave(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $user_id = $this->_adminSession->hasUser() ? (int)$this->_adminSession->getUser()->getId() : null;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        //check credit limit
        $total_order = $order->getGrandTotal();
        $numberOfRepayments = $this->getNumerOfRepayment($order);
        $customer = $om->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());

        $saving = $order->getSavingAccount();
        if(empty($saving)){
            $saving = 0;
        }

        $amount = $order->getSavingAccountAmount();
        if (empty($amount)) {
            $amount = 0;
        }
        if ($amount >= $total_order) {
            $order->setSavingAccountAmount($total_order);
            $amount = $total_order;
        }
        if ($user_id > 0 && $total_order > 0 && $order->getStatus() == self::ORDER_PENDING) {
            $productIds = [];
            foreach ($order->getItems() as $orderItem) {
                $productIds[] = $orderItem->getProductId();
            }
            $productIds = reset($productIds);
            Custom::create()->checkCreditDueLimit($total_order, $order->getCustomerId(), $saving, $amount, $productIds, $customer->getStoreId());
        }

        if ($order->getStatus() == self::ORDER_PENDING) {
            $customer = $om->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
            $loan = Mifos::create($customer)->processLoanForIcareMember($customer, $order->getGrandTotal(), $order->getId(), $order->getIncrementId(), $numberOfRepayments, $saving, $amount);
            if (isset($loan->loanId) && $loan->loanId > 0 || $loan == 'saving') {
                if (isset($loan->loanId) && $loan->loanId > 0) {
                    $order->setLoanId($loan->loanId);
                }
            } else {
                $order->setStatus(self::ORDER_PENDING);
            }
        }

        // telesale auto confirm
        if ($order->getData('auto_confirmation')) {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }


    }

    /**
     * After save order
     */
    public function afterSave(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($order->getStatus() == self::ORDER_PENDING) {
            Mifos::create()->approveLoan($order->getLoanId(), $order->getId());
            // we must withdraw saving when order create
            if ($order->getSavingAccountAmount() > 0 && $order->getSavingAccount() == 1 && $order->getSavingTransactionId() == 0) {
                //withdraw amount from saving account
                $this->_logger->info(sprintf('Deduct saving account[orderNo=%s,savingAmount=%s]',
                    $order->getIncrementId(), $order->getSavingAccountAmount()));
                $response = Mifos::create()->withdrawAmount($order->getCustomerId(),
                    $order->getId(), $order->getSavingAccountAmount());
                $this->updateSavingTransactionId($response, $order->getId());

            }
        }
        if ($order->getLoanId() > 0) {
            if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                if ($order->getLoanId() > 0) {
                    //active Loan
                    Mifos::create()->activateLoan($order->getLoanId(), $order->getId());
                }
                //set status to confirm

                $order->setStatus(self::ORDER_CONFIRMED);
                $order->save();

            }
        }
        $this->updateOrderItemWithInstallmentInformation($order);

    }

    protected function updateSavingTransactionId($response, $orderId)
    {
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface $connection
         */
        $connection = $resource->getConnection();
        $connection->update('sales_order', [
            'saving_transaction_id' => isset($response->resourceId) ? $response->resourceId : 0
        ], [
            'entity_id = ?' => $orderId
        ]);
    }

    /**
     * Check Shipment before goto mifos
     * @param int $incrementId
     * @return bool
     */
    protected function checkShipmentExisted($incrementId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $urlObject = $om->get('Magento\Framework\UrlInterface');
        $currentUrl = $urlObject->getCurrentUrl();
        $shipmentObject = $om->get('Magento\Sales\Model\Order\Shipment');
        $shipment = $shipmentObject->loadByIncrementId($incrementId);
        if ($shipment->getOrderId() == NULL && strpos($currentUrl, self::ORDER_SHIPMENT) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Order Item Id
     * @param int $order_id
     * @return int
     */
    protected function getItembyOrderId($order_id)
    {
        $type = 'virtual';
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['order_id' => $order_id, 'product_type' => $type];
        $select = $connection->select()->from(
            'sales_order_item',
            ['item_id']
        );
        $select->where('order_id = :order_id');
        $select->where('product_type = :product_type');
        $itemId = $connection->fetchOne($select, $bind);
        if ($itemId) {
            return $itemId;
        } else {
            return null;
        }
    }

    protected function getInstallmentProduct($productId, $storeId)
    {
        /**
         * @var \Icare\Installment\Helper\Data $installmentHelper
         */
        $installmentHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Icare\Installment\Helper\Data');
        $installment = $installmentHelper->getInstallmentProduct($productId, $storeId);
        $installment = reset($installment);
        return $installment;
    }

    protected function getNumerOfRepayment($order)
    {
        $productIds = [];
        foreach ($order->getItems() as $orderItem) {
            $productIds[] = $orderItem->getProductId();
        }
        $productIds = reset($productIds);

        $installmentInfo = $this->getInstallmentProduct($productIds, $order->getStoreId());
        return $installmentInfo['number_of_repayment'];
    }

    protected function updateOrderItemWithInstallmentInformation($order)
    {
        $orderItemObj = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Sales\Model\Order\Item');
        foreach ($order->getItems() as $orderItem) {
            $orderItem = $orderItemObj->load($orderItem->getId());
            $numberOfRepayment = $orderItem->getInstallmentNumberOfRepayment();
            if (!isset($numberOfRepayment)) {
                $installmentInfo = $this->getInstallmentProduct($orderItem->getProductId(), $order->getStoreId());
                $orderItem->setInstallmentNumberOfRepayment($installmentInfo['number_of_repayment']);
                $orderItem->setInstallmentInformation(json_encode($installmentInfo));
                $orderItem->save();
            }

        }
    }

}