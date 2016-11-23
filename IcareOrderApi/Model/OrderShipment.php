<?php 

namespace Icare\IcareOrderApi\Model;

use Icare\EventOrder\Plugin\OrderPlugin;
use Magento\Framework\Exception\ValidatorException;

/**
 * 
 * @author Nam Pham
 *
 */
class OrderShipment 
{
    /**
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;
    
    /**
     * @var \Psr\Log\LoggerInterface $_logger
     */
    private $_logger;
    
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    private $_shipmentFactory;
    
    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    private $_labelGenerator;
   
    /**
     * 
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $_shipmentRepo;
    
    /**
     * 
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentRepo = $shipmentRepository;
    }
    
    /**
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @throws IcareWebApiException
     * @see \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save::execute()
     */
    public function generateShipment($order, $data = NULL) 
    {   
        $data = $data ? $data : \json_decode($this->_request->getContent(), TRUE);
        if (isset($data['item_fulfillment_id'])) {
            // verify if fulfillment is not recorded, if yes it must be verified with the same order
            /**
             * 
             * @var \Magento\Sales\Model\Order\Shipment $shipment
             */
            $shipment = $this->_shipmentRepo->create();
            $shipment->getResource()->load($shipment, $data['item_fulfillment_id'], 'fulfillment_id');
            if ($shipment->getId()) {
                $relatedOrderId = $shipment->getOrderId();
                if ($relatedOrderId !== $order->getId()) {
                    throw new \Magento\Framework\Exception\ValidatorException(__('item_fulfillment_id has been associated with a different order '.$relatedOrderId));
                }
                return $shipment;
            }
        }
        else {
            throw new \Magento\Framework\Exception\ValidatorException(__('item_fulfillment_id is missing'));
        }
        
        /**
         * Check order status
         */
        if ($order->getStatus() != \Icare\EventOrder\Plugin\OrderPlugin::ORDER_CONFIRMED) {
            $error = 'Invalid Order Status: '.$order->getStatus();
        }
        /**
         * Check shipment is available to create separate from invoice
         */
        elseif ($order->getForcedShipmentWithInvoice()) {
            $error = 'Cannot do shipment for the order separately from invoice.';
        }
        /**
         * Check shipment create availability
         */
        elseif (!$order->canShip()) {
            $error = 'Cannot do shipment for the order.';
        }
        if (isset($error)) {
            throw new \Magento\Framework\Exception\ValidatorException(__($error));
        }
        
        // create shipment and set fulfillment_id
        $shipment = $this->_shipmentFactory->create(
            $order,
            self::getItemQuantities($order, $data['fulfillment']),
            isset($data['tracking'])?$data['tracking']:null
        );
        $shipment->setFulfillmentId($data['item_fulfillment_id']);
        
        // save shipment
        if (!empty($data['comment_text'])) {
            $shipment->addComment(
                $data['comment_text'],
                isset($data['comment_customer_notify']),
                isset($data['is_visible_on_front'])
                );
        
            $shipment->setCustomerNote($data['comment_text']);
            $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
        }
        
        $shipment->register();
        $shipment->setShipmentStatus(\Icare\Sales\Api\ShipmentInterface::STATUS_NEW);
        $order->setCustomerNoteNotify(!empty($data['send_email']));
        
        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
        if ($isNeedCreateLabel) {
            $this->_labelGenerator->create($shipment, $this->_request);
        }
        
        // update order status
        $order->setStatus(OrderPlugin::ORDER_PACKED);
        if (isset($data['note'])) {
            $order->addStatusHistoryComment($data['note']);
        }
        $order->setIsInProcess(true);
        
        $transaction = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DB\Transaction');
        $transaction->addObject(
            $shipment
        )->addObject(
            $order
        )->save();
        
        return $shipment;
    }
    
    /**
     * fulfillment data should be like below
     * <pre>
     * {
     *   'SKU1' => 2
     *   'SKU2' => 1
     * }
     * </pre>
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param array $fulfillment
     * @return array mapping from order item ID to quantity
     */
    static private function getItemQuantities($order, $fulfillment) 
    {
        $sku2item = array();
        foreach ($order->getItems() as $item) {
            $sku = \Icare\NetSuite\Helper\Payload::getNetSuiteItemSku($item);
            $sku2item[$sku] = $item;
        }
        
        $id2qty = array();
        foreach ($fulfillment as $sku => $quantity) {
            if (empty($sku2item[$sku])) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('SKU is not found in order ' . $order->getIncrementId() . ': '.$sku));
            }
            $id2qty[$sku2item[$sku]->getItemId()] = $quantity;
        }
        
        return $id2qty;
    }
}