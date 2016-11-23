<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 8/9/16
 * Time: 2:04 PM
 */

namespace Icare\Cache\Model;


use Icare\Exception\Model\IcareException;
use Magento\Framework\App\Cache\Type\FrontendPool;

class IcareCache extends AbstractCache
{

    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'Icare';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'COLLECTION_DATA';

    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool);
    }

    protected function _getCache($cacheId)
    {
        // TODO: Implement _getCache() method.
    }

    protected function _refresh()
    {
        // TODO: Implement _refresh() method.
    }

    public static function getIdentifier($cacheName, $object) {
        return $cacheName . '_' . hash('sha1', serialize($object));
    }
}