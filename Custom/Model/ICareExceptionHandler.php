<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/1/16
 * Time: 3:57 PM
 */

namespace Icare\Custom\Model;


use Monolog\Logger;

class ICareExceptionHandler extends ICareBaseHandler
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/icare-exception.log';
}