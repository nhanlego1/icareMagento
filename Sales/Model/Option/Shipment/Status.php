<?php

namespace Icare\Sales\Model\Option\Shipment;

/**
 * Flat shipment status implmentation. This implementation is a quick work around which
 * required localization in the property file (i18n/locale.csv) with <strong>SHIPMENT_</strong> prefix.
 * 
 * <p><i>This approach does not allow correct sorting in localed language.</i></p>
 *
 * @author Nam Pham
 * @see \Magento\Sales\Model\ResourceModel\Order\Collection
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{  
    /**
     * 
     * @var string[]
     */
    static private $_options = array();
    
    /**
     * get associative array of numeric to textual value shipment statuses
     * @return array
     */
    public static function getOptionArray()
    {
        if (empty(self::$_options)) {
            $options = array();
            $shipmentRef = new \ReflectionClass('\Icare\Sales\Api\ShipmentInterface');
            $constants = $shipmentRef->getConstants();
            foreach ($shipmentRef->getConstants() as $name => $value) {
                if (strpos($name, 'STATUS_') !== FALSE) {
                    $options[$value] = __('SHIPMENT_'.$name);
                }
            }
            asort($options);
            self::$_options = $options;
        }
        return self::$_options;
    }
    
    public function toOptionArray()
    {
        $options = array();
        foreach (self::getOptionArray() as $key => $name) {
            $options[] = array(
                'value' => $key,
                'label' => $name,
            );
        }
        return $options;
    }
}
