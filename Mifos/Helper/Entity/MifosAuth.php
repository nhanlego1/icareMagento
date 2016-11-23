<?php
namespace Icare\Mifos\Helper\Entity;

//Json Anotation

class MifosAuth
{
    private $_requestURL;
    private $_userName;
    private $_passWord;
    private $_accessToken;
    private $_ternant;

    public function __construct($requestURL, $userName, $passWord, $ternant, $accessToken = null)
    {
        $this->_requestURL = $requestURL;
        $this->_userName = $userName;
        $this->_passWord = $passWord;
        $this->_ternant = $ternant;
        $this->_accessToken = $accessToken;
    }

    public function getTernant()
    {
        return $this->_ternant;
    }

    public function getRequestURL()
    {
        return $this->_requestURL;
    }

    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    public function getUserName()
    {
        return $this->_userName;
    }

    public function getPassword()
    {
        return $this->_passWord;
    }

    public function getHeaders()
    {
        $headers = array('Content-Type: application/json');
        $accesstoken = $this->getAccessToken();
        $ternant = $this->getTernant();

        if (isset($accesstoken)) {
            $headers[] = 'Authorization: Basic ' . $accesstoken;
        }
        if (isset($ternant)) {
            $headers[] = 'Fineract-Platform-TenantId:' . $ternant;
        }

        return $headers;
    }


}

