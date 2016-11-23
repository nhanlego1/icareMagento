<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */
namespace Icare\Sales\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Customer\Api\CustomerMetadataInterface;

class Columns extends \Magento\Customer\Ui\Component\Listing\Columns
{

    private $visibleColumns = [
        'entity_id',
        'name',
        'billing_telephone',
        'billing_postcode',
        'billing_country_id',
        'billing_region',
        'website_id'
    ];
    /**
     * @param array $attributeData
     * @param string $columnName
     * @return void
     */
    public function addColumn(array $attributeData, $columnName)
    {
        if (in_array($columnName, $this->visibleColumns)) {
            $config['sortOrder'] = ++$this->columnSortOrder;
            if ($attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]) {
                $config['filter'] = $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]);
            }
            $column = $this->columnFactory->create($attributeData, $columnName, $this->getContext(), $config);
            $column->prepare();
            $this->addComponent($attributeData[AttributeMetadata::ATTRIBUTE_CODE], $column);
        }
    }

    /**
     * @param array $attributeData
     * @param string $newAttributeCode
     * @return void
     */
    public function updateColumn(array $attributeData, $newAttributeCode)
    {
        $component = $this->components[$attributeData[AttributeMetadata::ATTRIBUTE_CODE]];
        $this->addOptions($component, $attributeData);

        if ($attributeData[AttributeMetadata::BACKEND_TYPE] != 'static') {
            if ($attributeData[AttributeMetadata::IS_USED_IN_GRID]) {
                $config = array_merge(
                    $component->getData('config'),
                    [
                        'name' => $newAttributeCode,
                        'dataType' => $attributeData[AttributeMetadata::BACKEND_TYPE],
                        'visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID]
                    ]
                );
                if ($attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]) {
                    $config['filter'] = $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]);
                }
                $component->setData('config', $config);
            }
        } else {
            if ($attributeData['entity_type_code'] == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER
                && !empty($component->getData('config')['editor'])
            ) {
                $this->inlineEditUpdater->applyEditing(
                    $component,
                    $attributeData[AttributeMetadata::FRONTEND_INPUT],
                    $attributeData[AttributeMetadata::VALIDATION_RULES],
                    $attributeData[AttributeMetadata::REQUIRED]
                );
            }
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'),
                    ['visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID]]
                )
            );
        }
    }

    /**
     * Add options to component
     *
     * @param UiComponentInterface $component
     * @param array $attributeData
     * @return void
     */
    public function addOptions(UiComponentInterface $component, array $attributeData)
    {
        $config = $component->getData('config');
        if (count($attributeData[AttributeMetadata::OPTIONS]) && !isset($config[AttributeMetadata::OPTIONS])) {
            $component->setData(
                'config',
                array_merge($config, [AttributeMetadata::OPTIONS => $attributeData[AttributeMetadata::OPTIONS]])
            );
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
