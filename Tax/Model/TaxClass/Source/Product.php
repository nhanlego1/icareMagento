<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 28/10/2016
 * Time: 14:22
 */

namespace Icare\Tax\Model\TaxClass\Source;

use Magento\Framework\DB\Ddl\Table;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;

/**
 * Product tax class source model.
 */
class Product extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $_taxClassRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $_optionFactory;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $store
    ) {
        $this->_classesFactory = $classesFactory;
        $this->_optionFactory = $optionFactory;
        $this->_taxClassRepository = $taxClassRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_resource = $resource;
        $this->_request = $request;
        $this->_store = $store;
    }

    /**
     * Returns table name
     *
     * @param string|array $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->_resource->getTableName($name);
    }

    /**
     * Retrieve all product tax class options.
     *
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (!$this->_options) {
            $tablelTc = $this->getTable('tax_class');
            $tableCclt = $this->getTable('tax_calculation');
            $tableTcr = $this->getTable('tax_calculation_rate');
            $connection = $this->_resource->getConnection();

            $select = $connection->select()->from(
                $tablelTc,
                ['*']
            )->joinLeft(
                ['cclt' => $tableCclt],
                "(cclt.product_tax_class_id = {$tablelTc}.class_id)",
                []
            )->joinLeft(
                ['tcr' => $tableTcr],
                "(tcr.tax_calculation_rate_id = cclt.tax_calculation_rate_id)",
                []
            )->where(
                "{$tablelTc}.class_type = :class_type"
            )->group(
                "{$tablelTc}.class_id"
            );

            $storeId = $this->_request->getParam('store');
            if (!$storeId) {
                $binds = ['class_type' => TaxClassManagementInterface::TYPE_PRODUCT];
            } else {
                $store = $this->_store->getStore($storeId);
                $website = $this->_store->getWebsite($store->getWebsiteId());
                $countryCode = $website->getCode();
                $countryCode = strtoupper($countryCode);
                $select->where(
                    "tcr.tax_country_id = :country_id"
                );
                $binds = ['class_type' => TaxClassManagementInterface::TYPE_PRODUCT, 'country_id' => $countryCode];
            }
            $taxClasses = $connection->fetchAll($select, $binds);

            $this->_options[] = [
                'value' => '',
                'label' => __('None')
            ];
            foreach ($taxClasses as $taxClass) {
                $this->_options[] = [
                    'value' => $taxClass['class_id'],
                    'label' => $taxClass['class_name'],
                ];
            }
        }

        if ($withEmpty) {
            if (!$this->_options) {
                return [];
            } else {
                return $this->_options;
            }
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => $attributeCode . ' tax column',
            ],
        ];
    }

    /**
     * Retrieve Select for update attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        /** @var $option \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option */
        $option = $this->_optionFactory->create();
        return $option->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}