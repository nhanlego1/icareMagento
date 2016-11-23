<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 20/10/2016
 * Time: 10:58
 */

namespace Icare\Sales\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check order owner
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\User\Model\User $user
     * @return bool
     */
    public function checkOrderOwner($order, $user)
    {
        if (!$user->getId() || !$order->getId()) {
            return false;
        }

        if ($order->getUserId() == $user->getId()) {
            return true;
        }
        return false;
    }
}