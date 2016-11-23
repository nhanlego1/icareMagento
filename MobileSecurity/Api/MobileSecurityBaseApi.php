<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Api;

use Icare\MobileSecurity\Model\MobileSecurity;
use Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Webapi\Request;

class MobileSecurityBaseApi
{

    /**
     * Requesting Customer Pincode
     *
     * @var null
     */
    private $pincode = null;

    /**
     *
     * @var Request $request
     */
    protected $request;

    /**
     *
     * @var ObjectManager $om
     */
    protected $om;

    public function __construct(Request $request)
    {
        $this->om = ObjectManager::getInstance();
        $this->request = $request;
    }

    const APP_FIELD_SALE = "FIELDSALE";

    const APP_ICARE = "ICARE";

    /**
     * authorize
     *
     * @param
     *            $customer_id
     * @param
     *            $device_id
     * @return bool
     */
    public function authorize($customer_id, $pincode = false,$device_id = null)
    {
        if ($this->request->getHeader('app-type')) {
            if (strtoupper($this->request->getHeader('app-type')) == self::APP_FIELD_SALE) {
                return true;
            }
        }
        if ($pincode == false) {
            $this->pincode = $this->request->getHeader('iCare-Pin');
        } else {
            $this->pincode = $pincode;
        }
        /*
         * May use it in the future
         *
        if ($device_id == null) {
            $this->device_id = $this->request->getHeader('iCare-Device-Id');
        } else {
            $this->device_id = $device_id;
        }
        */
        $result = array();
        if (! empty($this->pincode)) {
            /**@var \Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\Collection $mobileSecurityCollection**/
            $mobileSecurityCollection = $this->om->get(Collection::className());
            $mobileSecurityCollection->addFieldToFilter('customer_id', $customer_id);
//             if($device_id != null)
//                 $mobileSecurityCollection->addFieldToFilter('device_id', $device_id);
            $mobileSecurityCollection->addFieldToFilter('pincode', $this->pincode);
            $models = $mobileSecurityCollection->load()->getItems();
            if (count($models) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * response
     *
     * @param
     *            $result
     * @return array
     */
    protected function response($result)
    {
        return [
            'result' => $result
        ];
    }
}