<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/2/2016
 * Time: 5:30 PM
 */
namespace Icare\Cms\Api;

interface PageInterface {

    /**
     * Get infor order
     * @param string $categoryId
     * @param string $websiteId
     * @return mixed
     */
    public function pageList($categoryId, $websiteId);

    /**
     * @api
     * @param int $pageId
     * @param string $type
     * @param int $number
     * @param int $customerId
     * @param string $ratingType
     * @param int $entityId
     * @param string $data
     * @return mixed
     */
    public function pageVote($pageId, $type, $number, $customerId = null, $ratingType = null, $entityId = null, $data = null);
}