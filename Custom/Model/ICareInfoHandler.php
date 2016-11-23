<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/1/16
 * Time: 3:56 PM
 */

namespace Icare\Custom\Model;

use Monolog\Logger;

class ICareInfoHandler extends ICareBaseHandler
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/icare-info.log';
//    public function __construct()
//    {
//        $client = DynamoDbClient::factory([
//            "region" => "localhost",
//            "endpoint" => "http://localhost:8000",
//            'credentials' => array(
//                'key'    => 'x',
//                'secret' => 'x',
//            ),
////            "access_key_id" => "x",
////            "secret_access_key" => "x",
//            "version" => '2012-08-10'
//        ]);
////        $client = new DynamoDbClient([
////            "region" => "localhost",
////            "endpoint" => "http://localhost:8000",
////            "access_key_id" => "x",
////            "secret_access_key" => "x",
////            "version" => '2012-08-10'
////        ]);
//        parent::__construct($client, 'icare_log');
//    }
//
//    public function __construct(DriverInterface $filesystem, $filePath)
//    {
//        //parent::__construct($filesystem, $filePath);
////        $this->pushProcessor(new IntrospectionProcessor());
//    }
}