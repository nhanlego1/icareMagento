<?php
namespace Icare\NetSuite\Observer\Message;

use Icare\Catalog\Model\NetSuiteProduct;
use Icare\Catalog\Model\NetSuiteProductOption;
use Icare\NetSuite\Helper\Payload;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Observe NetSuite message about inventory item
 * @author Nam Pham
 *
 */
class InventoryItem implements ObserverInterface
{
    private $_icareProduct;
    
    private $_orderShipment;
    
    private $_logger;
    
    /**
     * 
     * @param \Icare\Catalog\Model\Product $icareProduct
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Icare\Catalog\Model\Product $icareProduct,
        \Psr\Log\LoggerInterface $logger
        ) {
        $this->_icareProduct = $icareProduct;
        $this->_logger = $logger;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $payload = $event->getDataByKey('payload');
        
        $product = self::convertPayloadProduct($payload);
        $saved = $this->_icareProduct->saveNetSuiteProduct($product);
        
        // log error
        $logger = $event->getDataByKey('logger') ? $event->getDataByKey('logger') : $this->_logger;
        $logger->info(sprintf('saved NetSuite product %s (%s) with entity_id %u', $product->getName(), $product->getSku(), $saved[0]['entity_id']));
    }
    
    /**
     * convert payload object to {@link NetSuiteProduct} object
     * @param stdClass $payload
     * @return NetSuiteProduct
     * @see NetSuiteProduct
     */
    private static function convertPayloadProduct($payload)
    {
        $nsProduct = new NetSuiteProduct();
        $mappings = [
            'setSku' => $payload->sku,
            'setName' => $payload->name,
            'setStatus' => $payload->status,
            'setOptions' => self::convertProductOptions($payload->options),
            'setOptionMatrix' => $payload->option_matrix,
            'setCreatedAt' => $payload->created_at,
            'setUpdatedAt' => $payload->updated_at,
        ];
        
        foreach ($mappings as $setter => $value) {
            if ($value !== null) {
                $method = new \ReflectionMethod('Icare\Catalog\Model\NetSuiteProduct', $setter);
                $method->invoke($nsProduct, $value);
            }
        }
        
        return $nsProduct;
    }
    
    /**
     * 
     * @param array $options
     * @return NetSuiteProductOption[]
     */
    private static function convertProductOptions($options)
    {
        if (empty($options)) return null;
        $nsOpts = [];
        
        foreach ($options as $option) {
            $nsOpt = new NetSuiteProductOption();
            $nsOpt->setOptionKey($option['option_key']);
            $nsOpt->setTitle(isset($option['title'])?$option['title']:$option['option_key']);
            $nsOpt->setValues($option['values']);
            $nsOpts[] =  $nsOpt;
        }
        
        return $nsOpts;
    }
}
