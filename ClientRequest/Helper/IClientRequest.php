<?php
namespace Icare\ClientRequest\Helper;

Interface IClientRequest
{
    /**
     * @param $data
     * @param $method
     * @return mixed
     */
    public function execute($data, $method);
}