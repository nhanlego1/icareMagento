<?php
namespace Icare\NetSuite\Helper\Client;

/**
 * 
 * @author Nam Pham
 * @deprecated seince 2.0.6 use NetSuiteQueueInterface instead
 */
interface NetSuiteClientInterface
{
    
    /**
     * post a payload to NetSuite
     * @param string $api name of the api which is configure in system variables
     * @param mixed $payload data which will be serialized as JSON before submission
     */
    public function postToNetSuite(string $api, $payload);
    
}
