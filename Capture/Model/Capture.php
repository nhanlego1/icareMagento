<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/2/2016
 * Time: 5:29 PM
 */
namespace Icare\Capture\Model;

use Icare\Capture\Api\CaptureInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Magento\Framework\DataObject\IdentityInterface;

class Capture extends \Magento\Framework\Model\AbstractModel implements CaptureInterface, IdentityInterface{
    /**
     * CMS page cache tag
     */
const CACHE_TAG = 'customer_capture';

    /**
     * @var string
     */
    protected $_cacheTag = 'customer_capture';


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Icare\Capture\Helper\Data $heldeskHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_helpdeskHelper = $heldeskHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Icare\Capture\Model\ResourceModel\Capture');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Load ticket by customer id
     *
     * @param   string $customerId
     * @return  $this
     */
    public function loadByCustomerId($customerId)
    {
        $this->_getResource()->loadByCustomerId($this, $customerId);
        return $this;
    }
    /**
     * Load ticket by order id
     *
     * @param   string $orderId
     * @return  $this
     */
    public function loadByOrderId($orderId)
    {
        $this->_getResource()->loadByOrderId($this, $orderId);
        return $this;
    }
    /**
     * Load ticket by device id
     *
     * @param   string $deviceId
     * @return  $this
     */
    public function loadByDeviceId($deviceId)
    {
        $this->_getResource()->loadByDeviceId($this, $deviceId);
        return $this;
    }
    /**
     * @api
     * @param string $customerId
     * @param string $deviceId
     * @param string $deviceName
     * @param string $osVesion
     * @param string $appVersion
     * @param string $lat
     * @param string $long
     * @param string $orderId
     * @return mixed
     */
    public function addCustomer($customerId, $deviceId, $deviceName, $osVesion, $appVersion, $lat, $long, $orderId, $isIcareMemberConfirm = 1)
    {
        //example api: {"customerId":2,"deviceId":"12345","deviceName":"iphone6","osVersion":"9.3","appVesion":"123":"lat":"123456","long":"12345"}
        // TODO: Implement addCustomer() method.
        $result = [];
        if(empty($customerId) || $customerId==0 || !is_numeric($customerId)){
          $result[] = new IcareException(__("Empty customer Id."));
        }
        if(empty($deviceId)){
            $result[] = new IcareException(__("Empty device uuid."));
        }
        if(empty($deviceName)){
            $result[] = new IcareException(__("Empty device name."));
        }
        if(empty($orderId) || !is_numeric($orderId) || $orderId==0){
            $result[] = new IcareException(__("Empty device name."));
        }
        if($result){
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $capture = $om->create('\Icare\Capture\Model\Capture');
        try {
            $return = [];
            $capture->setCustomerId($customerId);
            $capture->setUuid($deviceId);
            $capture->setDeviceName($deviceName);
            $capture->setOsVersion($osVesion);
            $capture->setAppVersion($appVersion);
            $capture->setLat($lat);
            $capture->setLong($long);
            $capture->setOrderId($orderId);
            $capture->setCreatedAt(date('Y-m-d H:i:s'));
            $capture->setData('is_icaremember_confirm', $isIcareMemberConfirm);
            $capture->save();
            $return['data'] = array('id'=>$capture->getId());
            return $return;
        }catch(\Exception $ex){
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
    }

    /**
     * @Api
     * @param string $customerId
     * @return mixed
     */
    public function customerInfo($customerId)
    {
        // TODO: Implement customerInfo() method.
        $result = [];
        if(empty($customerId) || $customerId==0){
            $result[] = new IcareException(__("Empty customer Id."));
        }
        if($result){
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $capture = $om->create('\Icare\Capture\Model\Capture')->loadByCustomerId($customerId);
        $data = [];
        $data['capture'] = $capture->getData();
        return $data;

    }

    /**
     * @Api
     * @param string $orderId
     * @return mixed
     */
    public function orderInfo($orderId)
    {
        // TODO: Implement customerInfo() method.
        $result = [];
        if(empty($orderId) || $orderId==0 || !is_numeric($orderId)){
            $result[] = new IcareException(__("Empty customer Id."));
        }
        if($result){
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $capture = $om->create('\Icare\Capture\Model\Capture')->loadByOrderId($orderId);
        $data = [];
        $data['capture'] = $capture->getData();
        return $data;

    }
}