<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/19/16
 * Time: 1:56 PM
 */

namespace Icare\Custom\Controller\Rest\Router;

use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\RouterInterface;

class Route extends \Magento\Webapi\Controller\Rest\Router\Route
{
    /**
     * Split route by parts and variables
     *
     * @return array
     */
    protected function getRoutePartsWithVersion()
    {
        $result = [];
        $route = $this->route;
        $request =
        $selectParam = '/' . $this->route;
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $select = $connection->select()->from("icare_manageapi")
            ->where('api_url = ?', $selectParam);
        $rows = $connection->fetchAssoc($select);

        foreach ($rows as $r) {
            $route = trim($r['connect_url'], '/');
        }


        $routeParts = explode('/', $route);
        foreach ($routeParts as $key => $value) {
            if ($this->isVariable($value)) {
                $this->variables[$key] = substr($value, 1);
                $value = null;
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Check if current route matches the requested path
     *
     * @param Request $request
     * @return array|bool
     */
    public function match(Request $request)
    {
        /** @var \Magento\Framework\Webapi\Rest\Request $request */
        $pathParts = $this->getPathParts($request->getPathInfo());
        $routeParts = $this->getRoutePartsWithVersion();
        if (count($pathParts) <> count($routeParts)) {
            return parent::match($request);
        }

        $result = [];
        foreach ($pathParts as $key => $value) {
            if (!array_key_exists($key, $routeParts)) {
                return parent::match($request);
            }
            $variable = isset($this->variables[$key]) ? $this->variables[$key] : null;
            if ($variable) {
                $result[$variable] = urldecode($pathParts[$key]);
            } else {
                if ($value != $routeParts[$key]) {
                    return parent::match($request);
                }
            }
        }
        return $result;
    }
}