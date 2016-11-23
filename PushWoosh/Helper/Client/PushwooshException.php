<?php
namespace Icare\PushWoosh\Helper\Client;

use Icare\Exception\Model\IcareException;

class PushwooshException
{
    private static $_instance;

    public static function getInstance() {
        if(null === static::$_instance) {
            static::$_instance = new PushwooshException();
        }

        return static::$_instance;
    }

    public function convert($response)
    {
        $errorResult = array();
        $exeption = new IcareException($response->Messages);
        $errorResult[] = $exeption;

        return $errorResult;
    }
}