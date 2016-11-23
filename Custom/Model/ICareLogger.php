<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/1/16
 * Time: 3:52 PM
 */

namespace Icare\Custom\Model;


use Icare\Exception\Model\IcareException;

class ICareLogger extends \Magento\Framework\Logger\Monolog
{

    protected $_class;

    public function addRecord($level, $message, array $context = [])
    {
        //if ($this->get_calling_class()) {
            return parent::addRecord($level, $message, $context);
        //}
        //return false;
    }

    public function setClass($class) {
        $this->_class = $class;
    }

    protected  function get_calling_class() {
        if ($this->_class) {
            $reflect = new \ReflectionObject($this->_class);
            return $reflect->getNamespaceName();
        }
        return null;
    }

    public function track($message, array $context = array()) {
        try {
            if (!static::$timezone) {
                static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
            }

            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
            $ts->setTimezone(static::$timezone);
            $record = array(
                'message' => $message,
                'context' => $context,
                'level' => 'TRACK',
                'level_name' => 'TRACK',
                'channel' => $this->name,
                'datetime' => $ts,
                'extra' => array(),
            );

            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
            $jsonHandler = new ICareJsonHandler(new \Magento\Framework\Filesystem\Driver\File());
            $jsonHandler->handle($record);
        } catch (\Exception $ex) {
            print_r($ex->getMessage());
        }
        return true;
    }

}