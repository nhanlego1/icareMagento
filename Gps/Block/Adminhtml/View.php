<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 10/24/16
 * Time: 3:04 PM
 */

namespace Icare\Gps\Block\Adminhtml;

use Magento\Framework\UrlInterface;

class View extends \Magento\Framework\View\Element\Template
{
    const API_KEY = 'google_api_key';
    const CURRENT_GPS_ID = 'current_gps_id';
    const GPS_URL_PATH_INDEX = 'icare/gps/index';

    private $indexUrl;

    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        $indexUrl = self::GPS_URL_PATH_INDEX,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_request = $context->getRequest();
        $this->_storeManager = $context->getStoreManager();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->indexUrl = $indexUrl;
        $this->_variables = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Variable\Model\Variable');
        parent::__construct($context, $data);
    }

    /**
     * get current id
     */
    public function getLocationId(){
        return $this->_request->getParam('gps_id', $this->_request->getParam('id', false));
    }

    /**
     * @return mixed
     */
    public function getLatlong(){
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      $gps = $om->get('Icare\Gps\Model\Gps')->load($this->getLocationId());
      return $gps->getData();
    }

    /**
     * get variable key api
     */
    public function getGoogleApiKey(){
        $store = $this->getCurrentStore();
        $key = $this->_variables->setStoreId($store->getStoreId())->loadByCode(self::API_KEY)->getPlainValue();
        return $key;
    }

    /**
     * get store
     */
    public function getCurrentStore(){
        return $this->_storeManager->getStore(); // give the information about current store
    }

    /**
     * get url
     */
    public function getUrlIndex(){
      $url =  $this->urlBuilder->getUrl($this->indexUrl);
        return $url;
    }
}
