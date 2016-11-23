<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Icare\User\Api;

interface UserApiInterface 
{
    /**
     * @api
     * @param \Icare\User\Api\Data\UserInfoInterface $userInfo
     * @return string
     */
    public function login(Data\UserInfoInterface $userInfo);
}
?>