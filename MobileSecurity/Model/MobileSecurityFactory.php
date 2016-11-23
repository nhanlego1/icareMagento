<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:14
 */

namespace Icare\MobileSecurity\Model;


use Magento\Framework\ObjectManager\Factory\AbstractFactory;

class MobileSecurityFactory extends AbstractFactory{
    
    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \LogicException
     * @throws \BadMethodCallException
     */
    public function create($requestedType, array $arguments = []) {
        // TODO: Implement create() method.
        return $this->objectManager->get(MobileSecurity::className());
    }

    /**
     * className
     * @return string
     */
    public static function className(){
        return get_called_class();
    }
}