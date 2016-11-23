<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/5/16
 * Time: 5:35 PM
 */

namespace Icare\Sales\Ui\Component\Listing\Column;


use Magento\Framework\Data\OptionSourceInterface;

class ICareCenterOptions implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    protected $_icareCenterCollection;

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct()
    {
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions() {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $icareCenterService = $om->get('Icare\Customer\Model\Customer');
        $authSession = $om->get('Magento\Backend\Model\Auth\Session');
        $storeId = $authSession->getUser()->getStoreId();
        if (empty($storeId)) {
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        } else {
            $store = $om->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);
        }
        
        $icare_center_list = $icareCenterService->getICareCenter($store->getWebsiteId());
        foreach ($icare_center_list  as $icare_center) {
            $this->currentOptions[$icare_center['address_id']]['label'] = $icare_center['full_name'];
            $this->currentOptions[$icare_center['address_id']]['value'] = $icare_center['address_id'];
        }
    }

}