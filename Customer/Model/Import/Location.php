<?php

namespace Icare\Customer\Model\Import;

use Icare\Customer\Api\CustomerInterface as ICareCenterInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

/**
 * Import Location data from NetSuite to Customer as iCare Center
 * @author Nam Pham
 * @since 2.3.36
 */
class Location extends \Magento\ImportExport\Model\Import\AbstractEntity
{
    const ERROR_CODE_STORE_NOT_FOUND = 'storeNotFound';
    const ERROR_CODE_WEBSITE_NOT_FOUND = 'websiteNotFound';
    
    /**#@+
     * Keys which used to build result data array for future update
     */
    const ENTITIES_TO_CREATE_KEY = 'entities_to_create';
    
    const ENTITIES_TO_UPDATE_KEY = 'entities_to_update';

    protected $masterAttributeCode = 'Internal ID';
    
    protected $_permanentAttributes = [
        'Internal ID',
        'Name',
        'City',
        'Country',
        'Subsidiary',
    ];
    
    protected $_specialAttributes = [
        'Internal ID',
        'Name (no hierarchy)', 
        'Name',
        'Parent Name',
        'Inactive',
        'Address', 'Address 1', 'Address 2', 'Address 3',
        'City',
        'State/Province',
        'Zip',
        'Country',
        'Phone',
        'Longitude',
        'Latitude',
        'Location Type',
        'Subsidiary'
    ];
    
    private $_fieldsMap = [
        'location_id' => 'Internal ID',
        AddressInterface::FIRSTNAME => ['Name (no hierarchy)', 'Name'],
        'parent_name' => 'Parent Name',
        'inactive' => 'Inactive',
        AddressInterface::STREET => ['Address', 'Address 1', 'Address 2', 'Address 3'],
        AddressInterface::CITY => 'City',
        AddressInterface::REGION => 'State/Province',
        AddressInterface::POSTCODE => 'Zip',
        AddressInterface::COUNTRY_ID => 'Country',
        AddressInterface::TELEPHONE => 'Phone',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
        'location_type' => 'Location Type',     // ~ store lookup
        'subsidiary' => 'Subsidiary'            // ~ website lookup
    ];
    
    /**
     * Customer fields in file
     */
    private $_customerFields = [
        'entity_id',
        CustomerInterface::STORE_ID,
        CustomerInterface::WEBSITE_ID,
        CustomerInterface::GROUP_ID,
        CustomerInterface::UPDATED_AT,
        CustomerInterface::CREATED_AT,
        CustomerInterface::CREATED_IN,
        CustomerInterface::PREFIX,
        CustomerInterface::FIRSTNAME,
        CustomerInterface::MIDDLENAME,
        CustomerInterface::LASTNAME,
        CustomerInterface::SUFFIX,
        CustomerInterface::EMAIL,
        'is_active',
        'icare_center_type'
    ];
    
    /**
     * Address fields in file
     */
    private $_addressFields = [
        'entity_id',
        AddressInterface::FIRSTNAME,
        AddressInterface::LASTNAME,
        AddressInterface::MIDDLENAME,
        AddressInterface::STREET,
        AddressInterface::CITY,
        AddressInterface::POSTCODE,
        AddressInterface::REGION,
        AddressInterface::REGION_ID,
        AddressInterface::COUNTRY_ID,
        AddressInterface::FAX,
        AddressInterface::TELEPHONE,
        AddressInterface::COMPANY,
        'parent_id',
        'is_active',
        'location_id',
    ];
    
    private $_requiredFields = [
        'location_id', 
        'firstname',
        'country_id',
        'subsidiary'
    ];
    
    private $_websiteIdCache = [];
    
    private $_storeIdCache = [];
    
    private $_icareCache = [];
    
    private $_storeManager;
    
    private $_customerFactory;
    
    private $_addressFactory;
    
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        array $data = []
        ) {
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);
        
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
    }
    
    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_address';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateRow()
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $rowData = $this->_customFieldsMapping($rowData);
        return $this->_validateRow($rowData, $rowNum);
    }
    
    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function _validateRow(array $rowData, $rowNum) 
    {
        //validate required fields 
        foreach ($this->_requiredFields as $requiredField) {
            if (empty($rowData[$requiredField])) {
                $column = $this->_fieldsMap[$requiredField];
                if (is_array($column)) $column = $column[0];
                $this->addRowError(self::ERROR_CODE_ATTRIBUTE_NOT_VALID, $rowNum, $column, null, ProcessingError::ERROR_LEVEL_CRITICAL);
            }
        }
        
        // stop if row does not have required data
        if ($this->getErrorAggregator()->isRowInvalid($rowNum)) return false;
        
        // validate website
        $websiteId = $this->getWebsiteId($rowData);
        if (!$websiteId) {
            $this->addRowError(self::ERROR_CODE_WEBSITE_NOT_FOUND, $rowNum, 'Subsidiary', 'Website not found for: '.$rowData['subsidiary'], ProcessingError::ERROR_LEVEL_CRITICAL);
            return false;
        }
        
        // validate store
        $storeId = $this->getStoreId($rowData);
        if (!$storeId) {
            $this->addRowError(self::ERROR_CODE_STORE_NOT_FOUND, $rowNum, 'Subsidiary', 'Store not found for: '.$rowData['firstname'], ProcessingError::ERROR_LEVEL_CRITICAL);
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param array $rowData
     * @return number|null identifier of website
     */
    private function getWebsiteId($rowData) 
    {
        $name = $rowData['subsidiary'];
        if (!isset($this->_websiteIdCache[$name])) {
            $websiteId = $this->_connection->select()
                ->from($this->_connection->getTableName('store_website'), 'website_id')
                ->where('name = ?', $name)
                ->orWhere('name = ?', $rowData['country_id'])
                ->query()
                ->fetchColumn(0);
            $this->_websiteIdCache[$name] = $websiteId;
        }
        return $this->_websiteIdCache[$name];
    }
    
    /**
     * get type of iCare Center
     * @param unknown $rowData
     * @return integer 
     */
    private function getICareCenterType($rowData)
    {
        return isset($rowData['location_type']) && $rowData['location_type'] == 'Warehouse'?
            ICareCenterInterface::ICARE_CENTER_TYPE_WAREHOUSE:ICareCenterInterface::ICARE_CENTER_TYPE_STORE;
    }
    
    /**
     * return object of iCare Center
     * @param array $rowData
     * @return \stdClass 
     */
    private function getICareCenterByData($rowData)
    {
        $storeId = $this->getStoreId($rowData);
        $type = $this->getICareCenterType($rowData);
        $cacheKey = "$storeId:$type";
        if (!isset($this->_icareCache[$cacheKey])) {
            $custEntity = $this->_connection->select()
                ->from(
                    ['customer' => $this->_connection->getTableName('customer_entity')],
                    $this->_customerFields)
                ->where("store_id = ?", $storeId)
                ->where("icare_center_type = ?", $type)
                ->limit(1)
                ->query()->fetch(\PDO::FETCH_ASSOC);
            
            // resolve from Name (no hierarchy) or Parent Name
            $storeName = isset($rowData['location_type']) && $rowData['location_type'] == 'Warehouse' ?
                (empty($rowData['parent_name'])?$rowData['firstname']:$rowData['parent_name']):$this->_storeManager->getStore($storeId)->getName();
            $storeType = $type == ICareCenterInterface::ICARE_CENTER_TYPE_WAREHOUSE? 'Warehouse':'Store';
            
            // update or insert row
            if (empty($custEntity)) {
                // create new customer
                $custEntity = array(
                    CustomerInterface::WEBSITE_ID => $this->getWebsiteId($rowData),
                    CustomerInterface::STORE_ID => $storeId,
                    CustomerInterface::FIRSTNAME => $storeName,
                    CustomerInterface::LASTNAME => $storeType,
                    'icare_center_type' => $type,
                );
                $custEntity = self::mergeArray($rowData, $custEntity, $this->_customerFields);
            }
            else {
                // update existing customer
                unset($rowData['entity_id']);
                $rowData = array_merge($rowData, [
                    CustomerInterface::FIRSTNAME => $storeName,
                    CustomerInterface::LASTNAME => $storeType,
                ]);
                $custEntity = self::mergeArray($custEntity, $rowData, $this->_customerFields);
            }
            $this->_icareCache[$cacheKey] = (object) $custEntity;
        }
        return  $this->_icareCache[$cacheKey];
    }
    
    /**
     *
     * @param array $rowData
     * @return number|null identifier of the website
     */
    private function getStoreId($rowData)
    {
        $websiteId = $this->getWebsiteId($rowData);
        // resolve from Name (no hierarchy) or Parent Name
        $storeName = isset($rowData['location_type']) && $rowData['location_type'] == 'Warehouse' ?$rowData['firstname']:
            (empty($rowData['parent_name'])?$rowData['subsidiary']:$rowData['parent_name']);
        
        $cacheKey = "$websiteId:$storeName";
        if (!isset($this->_storeIdCache[$cacheKey])) {
            // read if not cached
            $storeId = $this->_connection->select()
                ->from($this->_connection->getTableName('store'), ['store_id'])
                ->where('website_id = ?', $websiteId)
                ->where('name = ?', $storeName)
                ->limit(1)
                ->query()->fetchColumn(0);
            
            $this->_storeIdCache[$cacheKey] = $storeId;
        }
        return $this->_storeIdCache[$cacheKey];
    }
   
    
    /**
     * Create Product entity from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
        
            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->_validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }
                
                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    //@todo implement DELETE -> $entitiesToDelete
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $processedData = $this->prepareDataForUpdate($rowData);
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                }
            }
            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
            /**
             * Save prepared data
             */
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->saveLocationEntities($entitiesToCreate, $entitiesToUpdate);
            }
            if ($entitiesToDelete) {
                $this->deleteLocationEntities($entitiesToDelete);
            }
        }
        return true;
    }
    
    /**
     * insert or update a customer which stands for iCare Center
     * @param stdClass $custEntity
     */
    private function saveCustomerEntity($custEntity) 
    {
        if (empty($custEntity->entity_id) || empty($custEntity->_saved)) {
            // add random email because it is required
            if (empty($custEntity->email)) {
                $tableCust = $this->_connection->getTableName('customer_entity');
                $custStatus = $this->_connection->showTableStatus($tableCust);
                $custEntity->email = "icare.${custStatus['Auto_increment']}@icarebenefits.com";
            }
            $customer = $this->_customerFactory->create();
            if (empty($custEntity->entity_id)) {
                $customer->setData((array)$custEntity);
            }
            else {
                // load customer and modify
                $customer->load($custEntity->entity_id);
                foreach ((array)$custEntity as $attr => $value) {
                    $customer->setData($attr, $value);
                }
            }
            $customer->save();
            $custEntity->entity_id = $customer->getId();
            $custEntity->_saved = true;
        }
    }
    
    /**
     * save and update entities
     * @param array $entitiesToCreate
     * @param array $entitiesToUpdate
     */
    private function saveLocationEntities($entitiesToCreate, $entitiesToUpdate)
    {
        //insert entity
        foreach ($entitiesToCreate as $entity) {
            if (is_object($entity['parent_id'])) {
                $custEntity = $entity['parent_id'];
                $this->saveCustomerEntity($custEntity);
                $entity['parent_id'] = $custEntity->entity_id;
            }
            $address = $this->_addressFactory->create();
            $address->setData($entity);
            $address->save();
        }
        
        // update entities
        foreach ($entitiesToUpdate as $entity) {
            if (is_object($entity['parent_id'])) {
                $custEntity = $entity['parent_id'];
                $this->saveCustomerEntity($custEntity);
                $entity['parent_id'] = $custEntity->entity_id;
            }
            $address = $this->_addressFactory->create();
            $address->load($entity['entity_id']);
            unset($entity['entity_id']);
            foreach ($entity as $attr => $value) {
                $address->setData($attr, $value);
            }
            $address->save();
            //$this->_connection->update($tableAddr, $entity, 'entity_id = '.$entity_id);
        }
    }
    
    /**
     * delete entities
     * @param array $entitiesToDelete
     */
    private function deleteLocationEntities($entitiesToDelete)
    {
        //@todo implement this
        
    }
    
    /**
     * get address data from NetSuite location id
     * @param number $locationId
     * @return array
     */
    private function getAddressFromLocationId($locationId) 
    {
        $addr = $this->_connection->select()
            ->from($this->_connection->getTableName('customer_address_entity'), $this->_addressFields)
            ->where('location_id = ?', $locationId)
            ->forUpdate()
            ->query()
            ->fetch(\PDO::FETCH_ASSOC);
        return $addr;
    }
    
    /**
     * Prepare customer data for update
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareDataForUpdate(array $rowData)
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $savedAddr = $this->getAddressFromLocationId($rowData['location_id']);
        
        if ($savedAddr) {
            $toUpdate = self::mergeArray($savedAddr, $rowData, $this->_addressFields);
            $toUpdate['parent_id'] = $this->getICareCenterByData($rowData);
            $entitiesToUpdate[] = $toUpdate;
        }
        else {
            $toCreate = self::mergeArray($rowData, null, $this->_addressFields);
            $toCreate['parent_id'] = $this->getICareCenterByData($rowData);
            $entitiesToCreate[] = $toCreate;
        }
        
        return array(
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
        );
    }
    
    /**
     * 
     * @param array $current
     * @param array $update
     * @param array $keys
     */
    static function mergeArray($current, $update, $keys)
    {
        $result = array();
        foreach ($keys as $key) {
            if (isset($update[$key]) && $update[$key]!=='') {
                $result[$key] = $update[$key];
            }
            elseif (isset($current[$key])) {
                $result[$key] = $current[$key];
            }
        }
        return $result;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_prepareRowForDb()
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData = $this->_customFieldsMapping($rowData);
        $rowData = parent::_prepareRowForDb($rowData);
        
        // fill empty values for required fields
        $requiredFields = [
            AddressInterface::TELEPHONE => '',
            AddressInterface::LASTNAME => 'iCare Center',
        ];
        
        foreach ($requiredFields as $required => $value) {
            if (empty($rowData[$required])) $rowData[$required] = $value;
        }
        
        return $rowData;
    }
    
    /**
     * Custom fields mapping for changed purposes of fields and field names.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _customFieldsMapping($rowData)
    {
        $data = array();
        foreach ($this->_fieldsMap as $fieldName => $columns) {
            if (is_string($columns)) $columns = array($columns);
            foreach ($columns as $column) {
                if (array_key_exists($column, $rowData) && $rowData[$column]!=='') {
                    $data[$fieldName] = $rowData[$column];
                    break;
                }
            }
        }
    
        if (array_key_exists('inactive', $data) && !is_bool($data['inactive'])) {
            $data['is_active'] = $data['inactive'] == 'No'?'1':'0';
        }
        return $data;
    }
}
