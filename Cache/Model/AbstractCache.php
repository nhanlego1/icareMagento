<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 8/9/16
 * Time: 1:55 PM
 */

namespace Icare\Cache\Model;


use Magento\Framework\App\Cache\Type\Collection;
use Icare\Cache\Annotation;


abstract  class AbstractCache extends Collection
{
    protected abstract function _getCache($cacheId);
    protected abstract function _refresh();

    public function get($cacheId, $delimeter = '_') {
        $object = $this->load($cacheId);
        if ($object) {
            return $object;
        } else {
            return $this->_getCache($cacheId, $delimeter);
        }

    }

    public function refresh() {
        $this->_refresh();
    }


    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        $rs = parent::clean($mode, $tags);
        if ($mode == \Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
            if (in_array($this->getTag(), $tags)) {
                $this->_refresh();
            }
        } else if ($mode == \Zend_Cache::CLEANING_MODE_ALL) {
            $this->_refresh();
        }

        return $rs;
    }
}