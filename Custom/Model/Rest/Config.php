<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/19/16
 * Time: 1:52 PM
 */

namespace Icare\Custom\Model\Rest;

use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Config as ModelConfig;
use Magento\Webapi\Model\Config\Converter;


class Config extends \Magento\Webapi\Model\Rest\Config
{
    /**
     * Generate the list of available REST routes. Current HTTP method is taken into account.
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return Route[] matched routes
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function getRestRoutes(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $requestHttpMethod = $request->getHttpMethod();
        $servicesRoutes = $this->_config->getServices()[Converter::KEY_ROUTES];
        $routes = [];
        // Return the route on exact match
        if (isset($servicesRoutes[$request->getPathInfo()][$requestHttpMethod])) {
            $methodInfo = $servicesRoutes[$request->getPathInfo()][$requestHttpMethod];
            $routes[] = $this->_createRoute(
                [
                    self::KEY_ROUTE_PATH => $request->getPathInfo(),
                    self::KEY_CLASS => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS],
                    self::KEY_METHOD => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_METHOD],
                    self::KEY_IS_SECURE => $methodInfo[Converter::KEY_SECURE],
                    self::KEY_ACL_RESOURCES => array_keys($methodInfo[Converter::KEY_ACL_RESOURCES]),
                    self::KEY_PARAMETERS => $methodInfo[Converter::KEY_DATA_PARAMETERS],
                ]
            );
            return $routes;
        }
        $serviceBaseUrl = $this->_getServiceBaseUrl($request);


        ksort($servicesRoutes, SORT_STRING);
        foreach ($servicesRoutes as $url => $httpMethods) {
            $compare_url = $this->_replaceUrlVersion($url);
            // skip if baseurl is not null and does not match
            if (!$serviceBaseUrl || strpos(trim($compare_url, '/'), trim($serviceBaseUrl, '/')) !== 0) {
                // base url does not match, just skip this service
                // try to compare origin
                if (!$serviceBaseUrl || strpos(trim($url, '/'), trim($serviceBaseUrl, '/')) !== 0) {
                    continue;
                }
            }
            foreach ($httpMethods as $httpMethod => $methodInfo) {
                if (strtoupper($httpMethod) == strtoupper($requestHttpMethod)) {
                    $aclResources = array_keys($methodInfo[Converter::KEY_ACL_RESOURCES]);
                    $routes[] = $this->_createRoute(
                        [
                            self::KEY_ROUTE_PATH => $url,
                            self::KEY_CLASS => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS],
                            self::KEY_METHOD => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_METHOD],
                            self::KEY_IS_SECURE => $methodInfo[Converter::KEY_SECURE],
                            self::KEY_ACL_RESOURCES => $aclResources,
                            self::KEY_PARAMETERS => $methodInfo[Converter::KEY_DATA_PARAMETERS],
                        ]
                    );
                }
            }
        }

        return $routes;
    }

    protected function _replaceUrlVersion($url) {
        $baseUrlRegExp = '#^/V\d+#';
        $serviceBaseUrl = preg_replace($baseUrlRegExp,'/' ,$url);
        return $serviceBaseUrl;
    }
}