<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 11/10/2016
 * Time: 17:51
 */

namespace Icare\Customer\Block\Adminhtml\Edit\Tab\View;


use Icare\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;

class PersonalInfo extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo{
    public $_template = 'Icare_Customer::tab/view/personal_info.phtml';

    /**
     * Retrieve social ID
     *
     * @return string|null
     */
    public function getSocialId()
    {
        $customer = $this->getCustomer();
        /**@var \Magento\Customer\Model\Customer $customerModel **/
        $customerModel = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer')->load($customer->getId());

        return $customerModel->getData('social_id');
    }
    /**
     * Retrieve organization ID
     *
     * @return string|null
     */
    public function getOrganizationId()
    {
        $customer = $this->getCustomer();
        /**@var \Magento\Customer\Model\Customer $customerModel **/
        $customerModel = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer')->load($customer->getId());
        return $customerModel->getData('organization_id');
    }
}