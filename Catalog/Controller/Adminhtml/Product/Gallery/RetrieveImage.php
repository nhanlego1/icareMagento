<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 04/11/2016
 * Time: 11:11
 */

namespace Icare\Catalog\Controller\Adminhtml\Product\Gallery;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveImage extends \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage
{
    /**
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icare_Catalog::video_upload');
    }
}