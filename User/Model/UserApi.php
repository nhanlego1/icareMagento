<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Icare\User\Model;

use Icare\User\Api\UserApiInterface;
use Icare\Exception\Api\IExceptionInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class UserApi implements UserApiInterface
{
    /**
     * User Model
     *
     * @var UserModel
     */
    private $userModel;
    
    /**
     *
     * @var \Magento\Framework\Json\Helper\Data 
     */
    private $jsonEncode;
    /**
     * Initialize service
     *
     * @param Magento\User\Model\User $userModel
     */
    public function __construct(
        \Magento\User\Model\User $userModel,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->userModel = $userModel;
        $this->jsonEncode = $jsonEncode;
        $this->_scopeConfig = $scopeConfig;
    }
    
    /**
     * 
     * @param \Icare\User\Api\Data\UserInfoInterface $userInfo
     * @return string
     */
    public function login(\Icare\User\Api\Data\UserInfoInterface $userInfo) {
        // Example: {"userInfo": {"username":"test", "password":"test"}}
        $this->userModel->login($userInfo->getUsername(), $userInfo->getPassword());
        $store_id = $this->userModel->getStoreId();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeObject = $om->get('\Magento\Store\Model\Store');
        $store = $storeObject->load($store_id);

        $data = array();
        if (!$this->userModel->getId()) {
          $result[] = new IcareException(__("User does not exist or wrong password. Please try again."));
          throw new IcareWebApiException(402,__('Web Api Internal Error'), $result);
        } else {
            $autoConfirm = 0;
            if($this->userModel->getIsAllowedConfirmOrder() == 1){
                $autoConfirm =  $this->userModel->getIsAllowedConfirmOrder();
            }
            if($store->getData('store_is_autoconfirm') == 1){
                $autoConfirm = $store->getData('store_is_autoconfirm');
            }
            $country_code = $this->_scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $store_id);
            $data['websiteId'] = $store->getWebsiteId();
            $data['storeId'] = $store_id;
            $data['currency'] = $store->getDefaultCurrencyCode();
            $data['email'] = $this->userModel->getEmail();
            $data['code'] = $country_code;
            $data['user_id'] = $this->userModel->getId();
            $data['is_confirmed'] = $autoConfirm;
            $result['userInfo'] = $data;
        }
        return $result;
    }

}
