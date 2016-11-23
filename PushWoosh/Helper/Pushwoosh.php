<?php
namespace Icare\PushWoosh\Helper;

use Icare\PushWoosh\Helper\Client;
use Magento\Framework\Webapi\Exception;

class Pushwoosh
{
    const PUSHWOOSH_URL = 'pushwoosh_url';
    const PUSHWOOSH_APP = 'pushwoosh_app_code';
    const PUSHWOOSH_ACCESSTOKEN = 'pushwoosh_accesstoken';
    const IOS_TYPE = 1;
    const ANDROID_TYPE = 3;

    private static $_instance;
    private $_pushwooshURL;
    private $_appCode;
    private $_accessToken;

    public function __construct($pushwooshURL, $appCode, $accessToken)
    {
        $this->_pushwooshURL = $pushwooshURL;
        $this->_appCode = $appCode;
        $this->_accessToken = $accessToken;
    }

    public static function getInstance()
    {
        if(isset(static::$_instance)) {
            return static::$_instance;
        }

        $variables = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Variable\Model\Variable');
        $pushwooshURL = $variables->loadByCode(self::PUSHWOOSH_URL)->getPlainValue();
        $pushwooshApp = $variables->loadByCode(self::PUSHWOOSH_APP)->getPlainValue();
        $pushwooshAccessToken = $variables->loadByCode(self::PUSHWOOSH_ACCESSTOKEN)->getPlainValue();

        static::$_instance = new Pushwoosh($pushwooshURL, $pushwooshApp, $pushwooshAccessToken);
        return static::$_instance;
    }

    /**
     * Init data
     * @param $message
     * @param array $device_tokens
     * @param $data
     * @param $configs
     * @return string
     */
    function init_data($message, array $device_tokens, $data)
    {
        $notifications = array(
            'send_date' => 'now',
            'content' => $message,
            'data' => $data,
            'devices' => $device_tokens,
            'platforms' => array(self::ANDROID_TYPE, self::IOS_TYPE),
        );

        $requestBody = array(
            'application' => $this->_appCode,
            'auth' => $this->_accessToken,
            'notifications' => array($notifications),
        );

        return array('request' => $requestBody);
    }

    /**
     * Push notification
     * @param $message
     * @param array $device_tokens
     * @param object $data
     * @throws Exception
     */
    public function pushNotification($message, $device_tokens, $data)
    {
        $object = $this->init_data($message, $device_tokens, $data);
        $pushwoosh = Client\PushWooshClient::getInstance($this->_pushwooshURL);

        $pushwoosh->createMessage($object);
    }

    /**
     * How to use
     */
    private function test()
    {
        $message = 'Your order is complete';
        $data = new \stdClass();
        $data->order_id = 10;
        $data->name = 'ORDER 2000';

        $device_tokens = array('M_T1U:APA91bF4R7mGhEPyAvAHd2sYDHofy5GilNBN_UcKrhJWDlL4QZIcEltFdkX2ZuAtovwF1gg_P7gtmfq6XKj29_Awc4FvsQwx34HG7y1gYekevPlityLZNGeCk_ncctZK__HUA8hb6DRX');
        Pushwoosh::getInstance()->pushNotification($message, $device_tokens, $data);
    }
}