<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Model\ResourceModel;
class MobileSecurity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init('icare_mobile_security','id');

    }

    /**
     * className
     * @return string
     */
    public static function className(){
        return get_called_class();
    }
}
