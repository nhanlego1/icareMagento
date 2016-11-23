<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 17/10/2016
 * Time: 10:22
 */

namespace Icare\Sales\Model\ResourceModel\Order\Customer;

class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * Role helper
     *
     * @var \Icare\User\Helper\Role
     */
    protected $_roleHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Icare\User\Helper\Role $roleHelper
     * @param mixed $connection
     * @param string $modelName
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Icare\User\Helper\Role $roleHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_authSession = $authSession;
        $this->_roleHelper = $roleHelper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $user = $this->_authSession->getUser();
        $storeId = $user->getStoreId();
        $this->addNameToSelect()->addAttributeToSelect(
            'email'
        )->addAttributeToSelect(
            'created_at'
        )->joinAttribute(
            'billing_postcode',
            'customer_address/postcode',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_city',
            'customer_address/city',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_telephone',
            'customer_address/telephone',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_regione',
            'customer_address/region',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_country_id',
            'customer_address/country_id',
            'default_billing',
            null,
            'left'
        )->joinField(
            'store_name',
            'store',
            'name',
            'store_id=store_id',
            null,
            'left'
        )->joinField(
            'website_name',
            'store_website',
            'name',
            'website_id=website_id',
            null,
            'left'
        );
        $this->addFilter('icare_center_type', ['null' => true], 'public');
        if (!$this->_roleHelper->checkSpecialUser($user)) {
            $this->addFieldToFilter(
                'store_id',
                ['eq' => $storeId]
            );
        }
        return $this;
    }
}