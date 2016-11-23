<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/11/2016
 * Time: 15:11
 */

namespace Icare\Store\Model;


use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;

class Store extends \Magento\Store\Model\System\Store{
    private $authSession = null;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $authSession
    ) {
        parent::__construct($storeManager);
        $this->authSession = $authSession;
    }

    /**
     * getStoreValuesForForm
     * @param bool $empty
     * @param bool $all
     * @return array
     */
    public function getStoreValuesForForm($empty = FALSE, $all = FALSE) {
        $options = parent::getStoreValuesForForm($empty, $all);
        /**@var \Icare\Custom\Helper\ICareHelper $helper**/
        $helper = ObjectManager::getInstance()->get('Icare\Custom\Helper\ICareHelper');
        $user = $this->authSession->getUser();
        if ($user) {
            if(!$helper->checkSpecialUser($user)){
                $store_id = $user->getStoreId();
                foreach ($options as $websiteOption){
                    $hasStore = FALSE;
                    if(isset($websiteOption['value']) && is_array($websiteOption['value'])){
                        foreach ($websiteOption['value'] as $groupOption){
                            if($groupOption['value']==$store_id){
                                $hasStore = TRUE;
                                break;
                            }
                            if(isset($groupOption['value']) && is_array($groupOption['value'])){
                                foreach ($groupOption['value'] as $option){
                                    if($option['value']==$store_id){
                                        $hasStore = TRUE;
                                        break;
                                    }
            
                                }
                            }
            
                        }
                    }
                    if(!$hasStore) unset($options[array_search($websiteOption,$options)]);
            
                }
            }
        }
        
        return $options;
    }
}