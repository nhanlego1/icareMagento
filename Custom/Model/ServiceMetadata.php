<?php
namespace Icare\Custom\Model;

use Magento\Webapi\Model\Config\Converter;

/**
 * Service Metadata Model
 */
class ServiceMetadata extends \Magento\Webapi\Model\ServiceMetadata
{
    /**
     * Collect the list of services metadata
     *
     * @return array
     */
    protected function initServicesMetadata()
    {
        $services = [];
        try {
            foreach ($this->config->getServices()[Converter::KEY_SERVICES] as $serviceClass => $serviceVersionData) {
                if (stripos($serviceClass, 'icare') !== false) {
                    foreach ($serviceVersionData as $version => $serviceData) {
                        $serviceName = $this->getServiceName($serviceClass, $version);
                        foreach ($serviceData[Converter::KEY_METHODS] as $methodName => $methodMetadata) {
                            $services[$serviceName][self::KEY_SERVICE_METHODS][$methodName] = [
                                self::KEY_METHOD => $methodName,
                                self::KEY_IS_REQUIRED => (bool)$methodMetadata[Converter::KEY_SECURE],
                                self::KEY_IS_SECURE => $methodMetadata[Converter::KEY_SECURE],
                                self::KEY_ACL_RESOURCES => $methodMetadata[Converter::KEY_ACL_RESOURCES],
                            ];
                            $services[$serviceName][self::KEY_CLASS] = $serviceClass;
                        }
                         
                        $reflectedMethodsMetadata = $this->classReflector->reflectClassMethods(
                            $serviceClass,
                            $services[$serviceName][self::KEY_SERVICE_METHODS]
                            );
                        $services[$serviceName][self::KEY_SERVICE_METHODS] = array_merge_recursive(
                            $services[$serviceName][self::KEY_SERVICE_METHODS],
                            $reflectedMethodsMetadata
                            );
                        $services[$serviceName][Converter::KEY_DESCRIPTION] = $this->classReflector->extractClassDescription(
                            $serviceClass
                            );
                    }
                }
    
    
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());die;
        }
    
        return $services;
    }
}