<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/11/2016
 * Time: 11:33
 */

namespace Icare\Sales\Model\Order;


class Item extends \Magento\Sales\Model\Order\Item
{
    /**
     * afterSave
     * @return $this
     */
    public function beforeSave()
    {
        if (is_string($this->getData('product_options'))) {
            $this->setData('product_options', unserialize($this->getData('product_options')));
        }
        return parent::beforeSave(); // TODO: Change the autogenerated stub
    }

   
}