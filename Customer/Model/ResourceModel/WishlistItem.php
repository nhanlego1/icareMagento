<?php
namespace Icare\Customer\Model\ResourceModel;

class WishlistItem extends \Magento\Wishlist\Model\ResourceModel\Item
{
    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->isDeleted(false);
        $result = parent::save($object);
        return $result;
    }
}