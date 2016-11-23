<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 7/11/16
 * Time: 5:02 PM
 */
namespace Icare\Customer\Api;

use Icare\Customer\Model\Customer;

interface CustomerInterface
{
    /**
     * distribution center type
     * @var integer
     */
    const ICARE_CENTER_TYPE_WAREHOUSE = 1;
    
    /**
     * store type
     * @var integer
     */
    const ICARE_CENTER_TYPE_STORE = 2;

    /**
     * Search Customer
     *
     * @param string $keyword
     * @param  string $countryCode
     * @param  string $device_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function getList($keyword, $countryCode,$device_id);
    
    /**
     * Search Customer
     *
     * @param string $keyword
     * @param  string $countryCode
     * @param  string $device_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function getListCustomer($keyword, $countryCode,$device_id = null);

	/**
     * Get list of customer by their social ID
     * @api
     * @param string $social_id
     * @param  string $website_id
     * @param  string $store_id (optional)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function getListBySocialId($social_id, $website_id, $store_id = FALSE);

    /**
     *
     * @param string $telephone
     * @param string $website_id
     * @param string $store_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function getListByPhone($telephone, $website_id, $store_id);

    /**
     * @api
     * @param string $storeId
     * @param string $keyword
     * @return mixed
     */
    public function searchProducts($storeId, $keyword);

    /**
     * @api
     * @param string $customerId
     * @param string $creditLimit
     * @param string $dueLimit
     * @return mixed
     */
    public function customerCredit($customerId, $creditLimit, $dueLimit);

    /**
     * @api
     * @param string $websiteId
     * @return mixed
     */
    public function customerContent($websiteId);
    /**
     * @api
     * @param \Icare\Customer\Api\Data\CustomerInfoInterface $customerInfo
     * @return mixed
     */
    public function customerCreate(Data\CustomerInfoInterface $customerInfo);

    /**
     * @api
     * @param string $storeId
     * @param string $keyword
     * @param string $pageNum
     * @param string $pagesize
     * @return mixed
     */
    public function listProductSearchV3($storeId, $keyword, $pageNum, $pageSize);


    /**
     * add Deposit To Customer
     * @param integer $customer_id
     * @param integer $user_id
     * @param integer $amount
     * @return mixed
     */
    public function addDepositToCustomer($customer_id,$user_id,$amount);


    /**
     *
     * @param string $website_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function getICareCenter($website_id);

    /**
     * Login by Social ID
     *
     * @param string $social_id
     * @param  string $countryCode
     * @param  string $device_id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return mixed
     */
    public function loginBySocialId($social_id, $countryCode,$device_id = null);


}