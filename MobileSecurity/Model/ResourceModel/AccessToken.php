<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Model\ResourceModel;
class AccessToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init('icare_access_token','id');

    }

    /**
     * className
     * @return string
     */
    public static function className(){
        return get_called_class();
    }

    /**
     * load by customer Id
     */
    public function loadByCustomerId(\Icare\MobileSecurity\Model\AccessToken $token, $customer_id){
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customer_id];
        $select = $connection->select()
            ->from($this->getTable('icare_access_token'), 'id')
            ->where('customer_id=?', $customer_id);
        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($token, $id);
        } else {
            $token->setData([]);
        }

        return $this;
    }

    /**
     * load by customer Id
     */
    public function loadByToken(\Icare\MobileSecurity\Model\AccessToken $token, $access_token, $isLock = 0){
        $connection = $this->getConnection();
        $bind = ['access_token' => $access_token];
        $select = $connection->select()
            ->from($this->getTable('icare_access_token'), 'id')
            ->where('access_token=?', $access_token)
            ->where('is_lock=?', $isLock);
        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($token, $id);
        } else {
            $token->setData([]);
        }

        return $this;
    }
}
