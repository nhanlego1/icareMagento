<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 25/10/2016
 * Time: 14:32
 */

namespace Icare\Custom\Block\Adminhtml;


class ICareNotiWindow extends \Magento\AdminNotification\Block\Window{
    public function canShow() {
        $roles = $this->_authSession->getUser()->getRole()->getData();
        return (isset($roles['role_name']) && $roles['role_name'] == 'Administrators') && parent::canShow();
    }
}