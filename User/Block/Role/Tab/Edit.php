<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/14/16
 * Time: 10:15 AM
 */

namespace Icare\User\Block\Role\Tab;


class Edit extends \Magento\User\Block\Role\Tab\Edit
{
    public function getTree()
    {
        $rootArray = parent::getTree();
        if (!$rootArray || count($rootArray) <= 0) {
            $resources = $this->_aclResourceProvider->getAclResources();
            $rootArray = $this->_integrationData->mapResources(
                isset($resources[2]['children']) ? $resources[2]['children'] : []
            );
            return $rootArray;
        }
        return $rootArray;
    }
}