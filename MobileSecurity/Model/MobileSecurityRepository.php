<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Model;

use Icare\MobileSecurity\Api\MobileSecurityRepositoryInterface;
use Icare\MobileSecurity\Model\MobileSecurityInterface;
use Icare\MobileSecurity\Model\MobileSecurityFactory;
use Icare\MobileSecurity\Model\ResourceModel\MobileSecurity\CollectionFactory;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
class MobileSecurityRepository implements \Icare\MobileSecurity\Api\MobileSecurityRepositoryInterface
{
    protected $objectFactory;
    protected $collectionFactory;

    /**
     * MobileSecurityRepository constructor.
     * @param \Icare\MobileSecurity\Model\MobileSecurityFactory $objectFactory
     * @param \Icare\MobileSecurity\Model\MobileSecurityFactory $collectionFactory
     * @param $searchResultsFactory
     */
    public function __construct(
        MobileSecurityFactory $objectFactory,
        MobileSecurityFactory $collectionFactory,
        $searchResultsFactory
    )
    {
        $this->objectFactory        = $objectFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * save
     * @param \Icare\MobileSecurity\Model\MobileSecurityInterface $object
     * @return \Icare\MobileSecurity\Model\MobileSecurityInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(MobileSecurityInterface $object)
    {
        try
        {
            $object->save();
        }
        catch(\Exception $e)
        {
            throw new CouldNotSaveException($e->getMessage());
        }
        return $object;
    }

    /**
     * getById
     * @param $id
     * @return object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $object->load($id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;        
    }

    /**
     * delete
     * @param \Icare\MobileSecurity\Model\MobileSecurityInterface $object
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(MobileSecurityInterface $object)
    {
        try {
            $object->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;    
    }

    /**
     * deleteById
     * @param $id
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * getList
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);  
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }  
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];                                     
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;        
    }

    /**
     * className
     * @return string
     */
    public static function className(){
        return get_called_class();
    }
}
