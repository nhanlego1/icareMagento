<?php
namespace Icare\ClientRequest\Helper;
use Icare\ClientRequest\Helper\IClientRequest;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Mifos\Helper\Client\MifosException;
use Magento\Framework\Webapi\Exception;

class ClientRequest implements IClientRequest
{
    private static $_instance;

    public static function getInstance() {
        if(null === static::$_instance) {
            static::$_instance = new ClientRequest();
        }

        return static::$_instance;
    }

    /** Implement execute method
     * @param $data
     * @param $method
     */
    public function execute($data, $requestUrl, $headers = null, $method = 'GET', $is_array_parse = false) {
        $response = null;

        if (isset($data)) {
            $data = json_encode($data);
        }

        if (!isset($headers)) {
            $headers = array('Content-Type: application/json');
        }

        $client = new \Zend_Http_Client($requestUrl);
        $client->setHeaders($headers);
        $client->setRawData($data, 'application/json');
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        try {

            $response = $client->request($method);
            if ($response->getStatus() != 200) {
                $logger->info(print_r($response, true));
//                throw new IcareWebApiException($response->getStatus(), __('Misfos error'), []);
            }
            $response = $response->getBody();

        } catch(\Zend_Http_Client_Adapter_Exception $ex) {
            $logger->error($ex);
            $messages = MifosException::getInstance()->convertException($ex);
            throw new IcareWebApiException($ex->getCode(), $ex->getMessage(), $messages);
        }

        return isset($response) ? json_decode($response, $is_array_parse) : $response;
    }
}
