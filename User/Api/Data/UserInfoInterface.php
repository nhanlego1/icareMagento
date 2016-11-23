<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Icare\User\Api\Data;

interface UserInfoInterface
{
    /**
     * @return string
     */
    public function getUsername();
    
    /**
     * @return string 
     */
    public function getPassword();
    
    /**
     * 
     * @param type $username
     * 
     */
    public function setUsername($username);
    
    /**
     * 
     * @param type $password
     */
    public function setPassword($password);
}

?>

