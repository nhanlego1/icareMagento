<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 20/07/2016
 * Time: 10:25
 */

namespace Icare\Exception\Model;


use Icare\Exception\Api\IExceptionInterface;

class ExceptionManager implements IExceptionInterface
{
    /**+@
     * Exeption Structure render
             * {
            "message": "Web Api Internal Error",
            "errors": [
            {
            "message": "this is an error 1",
            "parameters": []
            },
            {
            "message": "this is an error 2",
            "parameters": []
            }
            ],
            "code": 1
            }
     */

    /**
     * @throws IcareWebApiException
     */
    public function renderException()
    {
        $exeption1 = new IcareException("this is an error 1");
        $exeption2 = new IcareException("this is an error 2");
        $errors = array();
        $errors[] = $exeption1;
        $errors[] = $exeption2;

        throw new IcareWebApiException(1,'Web Api Internal Error', $errors);
    }
}