<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/13/16
 * Time: 11:37 AM
 */

namespace Icare\Custom\Model;


//use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogstashFormatter;
//use r;

class ICareJsonHandler extends ICareBaseHandler
{

//    public function __construct(DriverInterface $filesystem, $filePath = null)
//    {
//        parent::__construct($filesystem, $filePath);
//        try {
//            $this->connection = r\connect('localhost');
//            if ($this->connection) {
//                try {
//                    r\dbCreate('icare_log')->run($this->connection);
//                } catch (\Exception $ex) {}
//            }
//            $this->connection->useDb('icare_log');
//            try {
//                $tableList = r\db('icare_log')->tableList()->run($this->connection);
//                $isExits = false;
//                foreach ($tableList as $table) {
//                    if ($table == 'log') {
//                        $isExits = true;
//                    }
//                }
//                if (!$isExits) {
//                    r\tableCreate('log')->run($this->connection);
//                }
//            } catch (\Exception $ex) {
//            }
//
//
//        } catch (\Exception $ex) {
//            print_r($ex->getMessage());
//        }
//
//    }

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/icare-track.log';

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return new LogstashFormatter('magento');
    }

    public function handle(array $record)
    {
        $record['formatted'] = $this->getFormatter()->format($record);
        parent::write($record);
//        // write to rethinkdb
//        unset($record['level_name']);
//        if (array() === $record['extra']) {
//            unset($record['extra']);
//        }
//
//        /** @var \DateTime $datetime */
//        $datetime = $record['datetime'];
//        $rDatetime = r\epochTime($datetime->getTimestamp());
//
//        $record['datetime'] = $rDatetime;
//
//        //$result =
//        r\table("log")->insert($record, ['durability' => 'soft'])->run($this->connection, ['noreply' => true]);
    }
}