<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/1/16
 * Time: 3:54 PM
 */

namespace Icare\Custom\Model;


use Monolog\Logger;

class ICareDebugHandler extends ICareBaseHandler
{

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/icare-debug.log';

}