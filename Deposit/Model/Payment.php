<?php
namespace Icare\Deposit\Model;
use Magento\Framework\Api\CustomAttributesDataInterface;

class Payment extends \Magento\Framework\Model\AbstractModel implements PaymentInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'icare_deposit_payment';

    protected function _construct()
    {
        $this->_init('Icare\Deposit\Model\ResourceModel\Payment');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode) {
        // TODO: Implement getCustomAttribute() method.
        return [];
    }

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue) {
        // TODO: Implement setCustomAttribute() method.
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes() {
        // TODO: Implement getCustomAttributes() method.
        return [];

    }

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setCustomAttributes(array $attributes) {
        // TODO: Implement setCustomAttributes() method.
    }
}
