<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/4/16
 * Time: 5:14 PM
 */

namespace Icare\Sales\Controller\Adminhtml\ICareCenter;


class Save extends \Magento\Customer\Controller\Adminhtml\Index\Save
{
    public function execute()
    {
        $resultRedirect =  parent::execute();
        $resultRedirect->setPath('sales/icarecenter/');
        return $resultRedirect;
    }
}