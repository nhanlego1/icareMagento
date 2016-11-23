<?php
/**
 * Created by PhpStorm.
 * User: VTV
 * Date: 7/20/16
 * Time: 2:38 PM
 */

namespace Icare\Mifos\Helper\Client;


use Icare\Exception\Model\IcareException;

class MifosException
{
    private static $_instance;

    public static function getInstance() {
        if(null === static::$_instance) {
            static::$_instance = new MifosException();
        }

        return static::$_instance;
    }

    public function parseMessage($response)
    {
        $errorResult = array();
        $errors = $response->errors;

        if(empty($errors)) {
            $exeption = new IcareException($response->defaultUserMessage);
            $errorResult[] = $exeption;

            return $errorResult;
        }

        foreach($errors as $error) {
            $exeption = new IcareException($error->defaultUserMessage);
            $errorResult[] = $exeption;
        }

        return $errorResult;
    }

    public function convertException($ex)
    {
        $errorResult = array();
        $exeption = new IcareException($ex->getMessage());
        $errorResult[] = $exeption;

        return $errorResult;
    }
}