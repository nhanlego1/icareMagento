<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/12/16
 * Time: 11:06 AM
 */

namespace Icare\Cms\Model\ResourceModel;


class Variables extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('variable', 'variable_id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setCode('cms_'.$object->getCode());
        $object->setName($object->getCode());
        return parent::_beforeSave($object);
    }

    /** Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        if ($object->getUseDefaultValue()) {
            /*
             * remove store value
             */
            $this->getConnection()->delete(
                $this->getTable('variable_value'),
                ['variable_id = ?' => $object->getId(), 'store_id = ?' => $object->getStoreId()]
            );
        } else {
            $data = [
                'variable_id' => $object->getId(),
                'store_id' => $object->getStoreId(),
                'plain_value' => $object->getPlainValue(),
                'html_value' => $object->getHtmlValue(),
            ];
            $data = $this->_prepareDataForTable(new \Magento\Framework\DataObject($data), $this->getTable('variable_value'));
            $this->getConnection()->insertOnDuplicate(
                $this->getTable('variable_value'),
                $data,
                ['plain_value', 'html_value']
            );
        }
        return $this;
    }

}