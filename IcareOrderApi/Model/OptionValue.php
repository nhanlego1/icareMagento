<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:40
 */

namespace Icare\IcareOrderApi\Model;

use Icare\IcareOrderApi\Api\Data\OptionValueInterface;

class OptionValue implements OptionValueInterface
{
    private $id;
    private $value;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $orderId 
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * @param string $value 
     */
    public function setValue($value) {
        $this->value = $value;
    }
}