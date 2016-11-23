<?php
namespace Icare\NetSuite\Helper\Entity;

//Json Anotation

class NetSuiteConfiguration
{
    const OAUTH_TOKEN = 'ns_oauth_token';
    const OAUTH_TOKEN_SECRET = 'ns_oauth_token_secret';
    const OAUTH_CONSUMER_KEY = 'ns_oauth_consumer_key';
    const OAUTH_CONSUMER_SECRET = 'ns_oauth_consumer_secret';
    
    const URL_ORDER_API = 'ns_url_order_api';
    const URL_HELLO_API = 'ns_url_hello_api';
    
    const TIMEOUT_IN_SECS = 'ns_timeout_in_secs';
    const ACCOUNT_ID = 'ns_account_id';
    
    private $_oauthToken;
    private $_oauthTokenSecret;
    private $_oauthConsumerKey;
    private $_oauthConsumerSecret;
    
    private $_urlOrderApi, $_urlHelloApi;
    
    private $_timeoutInSecs;
    private $_accountId;

    /**
     *
     * @param \Magento\Variable\Model\Variable $variables
     */
    public function __construct(\Magento\Variable\Model\Variable $variables)
    {
        $members = array(
            self::OAUTH_TOKEN => '_oauthToken',
            self::OAUTH_TOKEN_SECRET => '_oauthTokenSecret',
            self::OAUTH_CONSUMER_KEY => '_oauthConsumerKey',
            self::OAUTH_CONSUMER_SECRET => '_oauthConsumerSecret',
            self::URL_ORDER_API => '_urlOrderApi',
            self::URL_HELLO_API => '_urlHelloApi',
            self::TIMEOUT_IN_SECS => '_timeoutInSecs',
            self::ACCOUNT_ID =>'_accountId',
        );
        foreach ($members as $var => $member) {
            $var = $variables->loadByCode($var);
            if (!$var->getId()) {
                continue;
            }
            $this->$member = $var->getPlainValue();
        }
        
        $this->_timeoutInSecs = $this->_timeoutInSecs ? intval($this->_timeoutInSecs):30;
    }
    
    public function getAccountId()
    {
        return $this->_accountId;
    }

    public function getOauthToken()
    {
        return $this->_oauthToken;
    }

    public function getOauthTokenSecret()
    {
        return $this->_oauthTokenSecret;
    }

    public function getOauthConsumerKey()
    {
        return $this->_oauthConsumerKey;
    }

    public function getOauthConsumerSecret()
    {
        return $this->_oauthConsumerSecret;
    }

    public function getUrlOrderApi()
    {
        return $this->_urlOrderApi;
    }
    
    public function getUrlHelloApi()
    {
        return $this->_urlHelloApi;
    }

    public function getTimeoutInSecs()
    {
        return $this->_timeoutInSecs;
    }
}
