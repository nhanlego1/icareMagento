<?php

namespace Icare\PushWoosh\Helper\Client;

interface IPushwooshClient
{
    /**
     * Push message
     * @param $data
     * @return mixed
     */
    public function createMessage($data);

    /**
     * Register device
     * @param $data
     * @return mixed
     */
    public function registerDevice($data);
}
