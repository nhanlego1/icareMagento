<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 8/10/16
 * Time: 11:12 AM
 */

namespace Icare\Cache\Annotation;


use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;
use Icare\Exception\Model\IcareException;


class CacheAspect implements Aspect
{

    public function __construct()
    {
    }

    /**
     * @Around("@annotation(Icare\Cache\Annotation\Cacheable)")
     * @return mixed
     * @throws
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $this->_objecmanager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_cache = $this->_objecmanager->get('Icare\Cache\Model\IcareCache');
        $annotations = $invocation->getMethod()->getAnnotations();
        if ($annotations == null) {
            return $invocation->proceed();
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof \Icare\Cache\Annotation\Cacheable) {

                $cachName = $annotation->cacheName;
                $key = $annotation->key;
                if (!$cachName) {
                    throw new IcareException(__('cacheName is required'));
                }

                $args = $invocation->getArguments();
                if (!$key) {
                    if (count($args) > 0) {
                        $key = hash('sha1', serialize($args));
                    }
                } else {
                    // key is a name of param
                    $params = $invocation->getMethod()->getParameters();
                    $paramPosition = -1;
                    foreach ($params as $param) {
                        if ($param->getName() == $key) {
                            $paramPosition = $param->getPosition();
                            break;
                        }
                    }

                    if ($paramPosition >= 0) {
                        $key = hash('sha1', serialize($args[$paramPosition]));
                    }
                }
                $object = $this->_cache->load($cachName . '_' . $key);
                if ($object) {
                    return unserialize($object);
                } else {
                    $result = $invocation->proceed();
                    $this->_cache->save(serialize($result), $cachName . '_' . $key);
                    return $result;
                }
                break;
            }
        }

        return $invocation->proceed();
    }
}