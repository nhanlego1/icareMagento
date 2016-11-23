<?php
/**
 * Copyright © 2016 iCareBenefits . All rights reserved.
 * Created by: nhan_nguyen
 * Date: 09/11/2016
 * Time: 16:28
 */

namespace Icare\Catalog\Model\Plugin;

use Magento\ProductVideo\Setup\InstallSchema;

/**
 * Class External video entry processor
 */
class ExternalVideoEntryProcessor extends \Magento\ProductVideo\Model\Plugin\ExternalVideoEntryProcessor
{
    /**
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    protected function loadVideoDataById(array $ids, $storeId = null)
    {
        $mainTableAlias = $this->resourceEntryMediaGallery->getMainTableAlias();
        $joinConditions = $mainTableAlias.'.value_id = store_value.value_id';
        if (null !== $storeId) {
            $joinConditions = implode(
                ' AND ',
                [
                    $joinConditions,
                    'store_value.store_id = ' . $storeId
                ]
            );
        }
        $joinTable = [
            [
                ['store_value' => $this->resourceEntryMediaGallery->getTable(InstallSchema::GALLERY_VALUE_VIDEO_TABLE)],
                $joinConditions,
                $this->getVideoProperties()
            ]
        ];
        $result = $this->resourceEntryMediaGallery->loadDataFromTableByValueId(
            InstallSchema::GALLERY_VALUE_VIDEO_TABLE,
            $ids,
            $storeId,
            [
                'value_id' => 'value_id',
                'video_provider_default' => 'provider',
                'video_url_default' => 'url',
                'video_title_default' => 'title',
                'video_description_default' => 'description',
                'video_metadata_default' => 'metadata'
            ],
            $joinTable
        );
        foreach ($result as &$item) {
            $item = $this->substituteNullsWithDefaultValues($item);
        }

        return $result;
    }
}