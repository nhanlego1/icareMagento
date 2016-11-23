<?php
namespace Icare\Mifos\Helper;

use Icare\Mifos\Helper\Client\MifosClient;
use Icare\Mifos\Helper\Entity\MifosAuth;
use Magento\Framework\Webapi\Exception;

class Mifos
{
    const MIFOS_URL = 'mifos_url';
    const MIFOS_USER = 'mifos_username';
    const MIFOS_PASSWORD = 'mifos_password';
    const MIFOS_LOANPRODUCT = 'mifos_loanproduct';
    const MIFOS_LOANTEMPLATE = 'mifos_loantemplate';
    const MIFOS_SAVING_ACCOUNT = 'mifos_saving_productid';
    const MIFOS_AUTH = 'mifos_auth_order';

    private $_mifosClient;
    private static $_instance;
    private $_variables;

    /**
     * Mifos constructor.
     */
    function __construct()
    {
        $this->_mifosClient = MifosClient::getInstance();
        $this->_variables = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Variable\Model\Variable');
    }

    /**
     * Create Mifos instance
     * @return mixed
     */
    public static function create()
    {
        if (null === static::$_instance) {
            static::$_instance = new Mifos();
        }

        return static::$_instance;
    }

    /**
     * Read configuration to connect mifos
     * @param $storeId
     * @param $storeCode
     * @return MifosAuth
     */
    public function readConfiguration($storeId, $storeCode)
    {
        $mifosURL = $this->_variables->setStoreId($storeId)->loadByCode(self::MIFOS_URL)->getPlainValue();
        $userName = $this->_variables->setStoreId($storeId)->loadByCode(self::MIFOS_USER)->getPlainValue();
        $passWord = $this->_variables->setStoreId($storeId)->loadByCode(self::MIFOS_PASSWORD)->getPlainValue();
        return new MifosAuth($mifosURL, $userName, $passWord, $storeCode);
    }

    /**
     * Get ternant for request to Mifos
     * @param $websiteId
     * @return mixed
     */
    private function getWebsiteCode($websiteId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $website = $objectManager->create('Magento\Store\Model\Website')->load($websiteId);

        return $website->getCode();
    }

    /**
     * Get loan product from cache or Mifos directly
     * @param $loanProductId
     * @param MifosAuth $mifosAuth
     * @return mixed
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    private function getLoanProduct($loanProductId, MifosAuth $mifosAuth)
    {
        $objecmanager = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $objecmanager->get('Icare\Mifos\Model\Cache');

        $cacheKey = self::MIFOS_LOANPRODUCT . '_' . $mifosAuth->getTernant();
        $loanProduct = $cache->load($cacheKey);
        if (empty($loanProduct)) {
            $loanProduct = $this->_mifosClient->getLoanProduct($loanProductId, $mifosAuth);
            $cache->save(serialize($loanProduct), $cacheKey);

            return $loanProduct;
        }

        return unserialize($loanProduct);
    }

    /**
     * Get Loan Template
     * @param $mIcareMemberId
     * @param $loanProductId
     * @param MifosAuth $mifosAuth
     * @return array|mixed
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    private function getLoanTemplate($mIcareMemberId, $loanProductId, MifosAuth $mifosAuth)
    {
        $objecmanager = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $objecmanager->get('Icare\Mifos\Model\Cache');

        $cacheKey = self::MIFOS_LOANTEMPLATE . '_' . $mifosAuth->getTernant();
        $loanTemplate = $cache->load($cacheKey);

        if (empty($loanTemplate)) {
            $loanTemplate = $this->_mifosClient->getTemplateLoan($mIcareMemberId, $loanProductId, $mifosAuth);
            $cache->save(serialize($loanTemplate), $cacheKey);

            return $loanTemplate;
        }

        return unserialize($loanTemplate);
    }

    /**
     * Set client id to customer
     * @param $icareMember
     * @param $clientId
     */
    private function updateClientIdToIcareMember($icareMember, $clientId)
    {
        $currentClientId = 0;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customerRepository = $om->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerRe = $customerRepository->getById($icareMember->getId());
        if ($customerRe->getCustomAttribute('client_id')) {
            $clientId = $customerRe->getCustomAttribute('client_id')->getValue();
        }

        if ($currentClientId === $clientId) {
            return;
        }

        $customerData = $icareMember->getDataModel();
        $customerData->setCustomAttribute('client_id', $clientId);

        $icareMember->updateData($customerData);
        $icareMember->save();
    }

    /**
     * Get Mifos Authentication Object from Cache
     * @param $orderId
     * @return MifosAuth|null
     */
    private function getMifosAuth($orderId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cacheKey = self::MIFOS_AUTH . '_' . $orderId;
        $objecmanager = \Magento\Framework\App\ObjectManager::getInstance();

        $cache = $objecmanager->get('Icare\Mifos\Model\Cache');
        $mifosAuth = $cache->load($cacheKey);

        if (empty($mifosAuth)) {
            $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
            $icareMember = $om->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
            $storeId = $icareMember->getStoreId();
            $storeCode = $this->getWebsiteCode($icareMember->getWebsite_id());
            $mifosAuth = $this->readConfiguration($storeId, $storeCode);

            $accesstoken = $this->_mifosClient->login($mifosAuth);
            $mifosAuth->setAccessToken($accesstoken);
            $this->setMifosAuth($mifosAuth, $orderId);

        }else{
            $data = unserialize($mifosAuth);
            $mifosAuth = new MifosAuth($data['requestURL'], null, null, $data['ternant'], $data['accessToken']);
        }


        $cache->remove($cacheKey);
        return $mifosAuth;
    }

    /**
     * Get Mifos Authentication Object from Cache
     * @param $orderId
     * @return MifosAuth|null
     */
    private function getMifosAuthClient($cutomerId)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cacheKey = self::MIFOS_AUTH . '_' . $cutomerId;
        $objecmanager = \Magento\Framework\App\ObjectManager::getInstance();

        $cache = $objecmanager->get('Icare\Mifos\Model\Cache');
        $mifosAuth = $cache->load($cacheKey);

        if (empty($mifosAuth)) {

            $icareMember = $om->create('Magento\Customer\Model\Customer')->load($cutomerId);
            $storeId = $icareMember->getStoreId();
            $storeCode = $this->getWebsiteCode($icareMember->getWebsite_id());
            $mifosAuth = $this->readConfiguration($storeId, $storeCode);

            $accesstoken = $this->_mifosClient->login($mifosAuth);
            $mifosAuth->setAccessToken($accesstoken);
            $this->setMifosAuth($mifosAuth, $cutomerId);

        }else{
            $data = unserialize($mifosAuth);
            $mifosAuth = new MifosAuth($data['requestURL'], null, null, $data['ternant'], $data['accessToken']);
        }


        $cache->remove($cacheKey);
        return $mifosAuth;
    }

    /**
     * Set mifos authentication object to cache
     * @param MifosAuth $mifosAuth
     * @param $orderId
     */
    private function setMifosAuth(MifosAuth $mifosAuth, $orderId)
    {
        $cacheKey = self::MIFOS_AUTH . '_' . $orderId;
        $data = array(
            'requestURL' => $mifosAuth->getRequestURL(),
            'accessToken' => $mifosAuth->getAccessToken(),
            'ternant' => $mifosAuth->getTernant(),
        );

        $objecmanager = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $objecmanager->get('Icare\Mifos\Model\Cache');

        $cache->save(serialize($data), $cacheKey);
    }

    /**
     * Process client and loan when place order
     * @param $icareMember
     * @param $orderTotal
     * @return mixed
     * @throws Exception
     */
    public function processLoanForIcareMember($icareMember, $orderTotal, $orderId, $orderIncrementId = '', $numberOfRepayments = 0, $saving = false, $savingAmount = 0)
    {
        $storeId = $icareMember->getStoreId();
        $storeCode = $this->getWebsiteCode($icareMember->getWebsite_id());
        $mifosAuth = $this->readConfiguration($storeId, $storeCode);

        $accesstoken = $this->_mifosClient->login($mifosAuth);
        $mifosAuth->setAccessToken($accesstoken);
        $mIcareMemberId = $this->_mifosClient->searchIcareMember($icareMember->getId(), $mifosAuth);
        $this->setMifosAuth($mifosAuth, $orderId);

        $this->updateClientIdToIcareMember($icareMember, $mIcareMemberId);

        $loanProductId = $this->_variables->setStoreId($storeId)->loadByCode(self::MIFOS_LOANPRODUCT)->getPlainValue();
        $loanProduct = $this->getLoanProduct($loanProductId, $mifosAuth);

        $loanTemplate = $this->getLoanTemplate($mIcareMemberId, $loanProductId, $mifosAuth);

        $loan = $this->_mifosClient->applyLoanForIcareMemberV2($loanProduct, $mIcareMemberId, $orderTotal, $loanTemplate, $mifosAuth, $orderIncrementId, $numberOfRepayments, $saving, $savingAmount);


        return $loan;
    }

    /**
     * Approve Loan
     */
    public function approveLoan($loanId, $orderId){
        //approve to lock Loan
        if (!isset($loanId) || $loanId <= 0 ) {
            return;
        }
        $mifosAuth = $this->getMifosAuth($orderId);
        $this->_mifosClient->approveLoan($loanId, $mifosAuth);
    }

    /**
     * Active and disburse loan
     * @param $loanId
     * @param $orderId
     * @return bool
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    public function activateLoan($loanId, $orderId)
    {
        if (!isset($loanId) || $loanId <= 0 ) {
            return;
        }
        $mifosAuth = $this->getMifosAuth($orderId);
        $this->_mifosClient->disburseLoan($loanId, $orderId, $mifosAuth);

        return true;
    }

    /**
     * Cancel Loan
     * @param $loanId
     * @param $orderId
     * @return bool
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    public function cancelLoan($loanId, $orderId)
    {
        if (!isset($loanId) || $loanId <= 0 ) {
            return;
        }
        $mifosAuth = $this->getMifosAuth($orderId);
        $this->_mifosClient->cancelLoan($loanId, $orderId, $mifosAuth);
        return true;
    }


    /**
     * Get limit credit and installment
     * @param $clientId
     * @return mixed
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    public function getInstallment($customerId)
    {
        $mifosAuth = $this->getMifosAuthClient($customerId);
        $response = $this->_mifosClient->getInstallment($customerId, $mifosAuth);
        return $response;
    }

    /**
     * Get limit credit and installment
     * @param $clientId
     * @return mixed
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    public function getLoan($orderId, $loanId)
    {
        $mifosAuth = $this->getMifosAuth($orderId);
        $response = $this->_mifosClient->getLoan($loanId, $mifosAuth);

        if($response){
            return $response;
        }else{
            return null;
        }

    }

    /**
     * add deposit amount to saving account
     */
    public function addDeposit($customerId, $amount, $orderId = null){

        $mifosAuth = $this->getMifosAuthClient($customerId);
        $response = $this->_mifosClient->addDeposit($customerId, $amount,$mifosAuth, $orderId);
        return $response;
    }

    /**
     * transfer deposit to transaction after finish order
     */
    public function transferDeposit($customerId, $orderId, $amount = 0){
        $mifosAuth = $this->getMifosAuthClient($customerId);
        $response = $this->_mifosClient->transferDeposit($customerId, $orderId, $amount, $mifosAuth);
        return $response;
    }

    /**
     * withdraw amunt from saving account
     */
    public function withdrawAmount($customerId, $orderId, $amount = 0){
        $mifosAuth = $this->getMifosAuthClient($customerId);
        $response = $this->_mifosClient->withdrawAmount($customerId, $orderId, $amount, $mifosAuth);
        return $response;
    }
}