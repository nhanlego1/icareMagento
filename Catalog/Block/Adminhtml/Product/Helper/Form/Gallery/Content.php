<?php
namespace Icare\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    /**
     * @return string
     */
    public function getImagesJson()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (is_array($this->getElement()->getValue())) {
            $value = $this->getElement()->getValue();
            if (is_array($value['images']) && count($value['images']) > 0) {
                $images = array();
                $value_ids = array();
                foreach ($value['images'] as $image) {
                    $value_ids[] = $image['value_id'];
                }
                $return_value_ids = $this->filterStore($value_ids, $storeId);
                foreach ($value['images'] as &$image) {
                    if (in_array($image['value_id'], $return_value_ids)) {
                        $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                        $images[] = $image;
                    }
                }
                $value['images'] = $images;
                return $this->_jsonEncoder->encode($value['images']);
            }
        }
        return '[]';
    }
    
    protected function filterStore($value_ids, $store_id) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $select = $connection->select()->from('catalog_product_entity_media_gallery_value', ['value_id', 'store_id']);
        $select->where('value_id in (?)', $value_ids);
        $select->where('store_id = ?',  $store_id);
        $rows = $connection->fetchAssoc($select);
        $return_value_ids = array();
        foreach($rows as $row) {
            $return_value_ids[] = $row['value_id'];
        }
        return $return_value_ids;
    }
}