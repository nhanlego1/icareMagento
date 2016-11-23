<?php 

namespace Icare\Sales\Api;

/**
 * Extension of Magento\Sales\Order\Shipment
 * 
 * @author Nam Pham
 *
 */
interface ShipmentInterface
{
    /**
     * 
     * @var integer
     */
    const STATUS_DELIVERED = 0;
    
    /**
     * 
     * @var integer
     */
    const STATUS_NEW = 1;
    
    /**
     * 
     * @var integer
     */
    const STATUS_PACKED = 2;
    
    /**
     * 
     * @var integer
     */
    const STATUS_PICKED = 3;
    
    /**
     * cannot be delivered
     * @var integer
     */
    const STATUS_FAILED = 4;
    
}