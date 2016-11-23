<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 8/31/16
 * Time: 10:34 PM
 */

namespace Icare\Cron\Model;


class ExampleCron
{
    public function showCronRunning() {
        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('test example job');
        return $this;
    }
}