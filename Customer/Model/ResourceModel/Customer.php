<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Customer\Model\ResourceModel;

use Icare\Cache\Model\IcareCache;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

/**
 * Customer entity resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends \Magento\Customer\Model\ResourceModel\Customer
{

    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';

    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Icare\Cache\Model\IcareCache $icareCache
    )
    {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $scopeConfig, $validatorFactory, $dateTime, $storeManager);
        $this->request = $request;
        $this->_icareCache = $icareCache;
    }

    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            ['telephone', 'website_id']
        )->where(
            'entity_id = :entity_id');
        $bind = ['entity_id' => (int)$customer->getId()];
        $old_customer = $connection->fetchRow($select,$bind);
        parent::_beforeSave($customer);
        $this->_icareCache->remove(IcareCache::getIdentifier('customers', array($old_customer['telephone'], $old_customer['website_id'])));
        $telephone = null;
        $isNew = true;
        $address = $customer->getAddresses();
        if ($address) {
            $address = $customer->getDefaultShippingAddress();
            if (!isset($address)) {
                $address = $customer->getDefaultBillingAddress();
            }
            if ($address) {
                $address = $this->request->getParam('address');
                if ($address && is_array($address)) {
                    $address = reset($address);
                    $telephone = $address['telephone'];
                    $isNew = false;
                }
            }

        } else {
            $address = $this->request->getParam('address');
            if ($address && is_array($address)) {
                $address = reset($address);
                $telephone = $address['telephone'];
            }
        }
        if ($telephone) {
            if ($isNew) {
                if ($this->checkUniqueTelephoneWebsiteId($customer->getWebsiteId(), $telephone)) {
                    $result[] = new IcareException(__("The customer that have telephone " . $telephone . " is ready register. Please enter other telephone."));
                    throw new IcareWebApiException(401, __("The customer that telephone " . $telephone . " is ready register. Please enter other telephone."), $result);
                }
            }
        }
        return $this;
    }

    /**
     * Save customer addresses and set default addresses in attributes backend
     *
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $customer)
    {
        parent::_afterSave($customer);
      //  $this->updateTelephoneCustomer($customer);
        return $this;
    }

    /**
     * Check exit customer with phone and website_id
     * @param int $websiteId
     * @param string $telephone
     * @return mixed
     */
    protected function checkUniqueTelephoneWebsiteId($websiteId, $telephone)
    {
        $connection = $this->getConnection();
        $bind = ['telephone' => $telephone, 'website_id' => $websiteId];
        $select = $connection->select()->from(
            self::CUSTOMER_TABLE,
            [self::CUSTOMER_ENTITY_FIELD]
        );
        $select->where('telephone = :telephone');
        $select->where('website_id = :website_id');
        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update telephone to customer
     * @return mixed
     */
    protected function updateTelephoneCustomer($customer)
    {
        $telephone = NULL;
        $address = $customer->getDefaultShippingAddress();
        if (!isset($address)) {
            $address = $customer->getDefaultBillingAddress();
        }
        if ($address) {
            $address = $this->request->getParam('address');
            if ($address) {
                $address = reset($address);
                $telephone = $address['telephone'];
            }
        }
        if ($telephone) {
            $telephoneData = [];
            $telephoneData['telephone'] = $telephone;
            try {
                $this->getConnection()->update(
                    self::CUSTOMER_TABLE,
                    $telephoneData,
                    $this->getConnection()->quoteInto('entity_id = ?', $customer->getId())
                );
            } catch (\Exception $ex) {
                $result[] = new IcareException(__($ex->getMessage()));
                throw new IcareWebApiException(401, __("The phone number is ready exist. Please choose other phone number."), $result);
            }

        }
    }

}
