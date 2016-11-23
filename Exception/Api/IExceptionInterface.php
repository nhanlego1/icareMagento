<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 20/07/2016
 * Time: 10:24
 */

namespace Icare\Exception\Api;


interface IExceptionInterface
{
    /**
     * @return mixed
     */
    public function renderException();
}