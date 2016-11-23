<?php
namespace Icare\PushWoosh\Helper\Client;

use Icare\ClientRequest\Helper\ClientRequest;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class PushWooshClient implements IPushwooshClient
{
    private $_clientRequest;
    private static $_instance;
    private $_requestURL;

    const CREATEMESSAGE = 'createMessage';
    const REGISTERDEVICE = 'registerDevice';

    const PUSHWOOSH_ERROR = 'Pushwhoosh internal error';

    public function __construct($pushwooshURL)
    {
        $this->_clientRequest = new ClientRequest();
        $this->_requestURL = $pushwooshURL;
    }

    public static function getInstance($pushwooshURL)
    {
        if(null === static::$_instance)
        {
            static::$_instance = new PushWooshClient($pushwooshURL);
        }

        return static::$_instance;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Exception
     * @throws \Icare\Exception\Model\IcareWebApiException
     */
    public function createMessage($data)
    {
        $requestURL = $this->_requestURL . self::CREATEMESSAGE;
        $response = $this->_clientRequest->execute($data, $requestURL, null, 'POST');
        $status_code = $response->status_code;

        if($status_code != 200)
        {
            $messages = PushwooshException::getInstance()->convert($response);
            throw new IcareWebApiException($status_code, self::PUSHWOOSH_ERROR, $messages);
        }

        return true;
    }

    public function registerDevice($data)
    {
        // TODO: Implement registerDevice() method.
    }
}