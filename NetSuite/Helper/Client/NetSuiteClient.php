<?php 

namespace Icare\NetSuite\Helper\Client;

use Magento\Framework\Oauth\Helper\Oauth;
use Magento\Framework\Oauth\OauthInterface;

/**
 * Implementation of {@link NetSuiteClientInterface} which post data directly to NetSuite
 * @author Nam Pham
 *
 */
class NetSuiteClient implements NetSuiteClientInterface
{
    private $_oauthHelper;
    
    private $_date;
    
    private $_httpUtility;
    
    private $_netsuiteConf;
    
    /**
     *
     * @param \Icare\NetSuite\Helper\Entity\NetSuiteConfiguration $netsuiteConf
     * @param \Magento\Framework\Oauth\Helper\Oauth $oauthHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Zend_Oauth_Http_Utility|null $httpUtility
     */
    public function __construct(
        \Icare\NetSuite\Helper\Entity\NetSuiteConfiguration $netsuiteConf,
        \Magento\Framework\Oauth\Helper\Oauth $oauthHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Zend_Oauth_Http_Utility $httpUtility = null)
    {
        $this->_netsuiteConf = $netsuiteConf;
        $this->_oauthHelper = $oauthHelper;
        $this->_date = $date;
        $this->_httpUtility = $httpUtility ?: new \Zend_Oauth_Http_Utility();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \Icare\NetSuite\Helper\Client\NetSuiteClientInterface::postToNetSuite()
     */
    public function postToNetSuite(string $api, $payload)
    {
        if (strpos($api, 'http://') !== 0 && strpos($api, 'https://') !== 0) {
            $url = call_user_func_array(array($this->_netsuiteConf, 'getUrl'.$api), array());
            if ($url == false) {
                throw new \Magento\Framework\Exception\NotFoundException(__('No api found for ' . $api));
            }
        } else {
            $url = $api;
        }
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $authz = $this->buildAuthorizationHeader($url);
        
        $httpClient = new \Zend_Http_Client($url, array('timeout' => $this->_netsuiteConf->getTimeoutInSecs()));
        $httpResp = $httpClient
            ->setHeaders('Authorization', $authz)
            ->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json')
            ->setHeaders(\Zend_Http_Client::CONTENT_LENGTH, strlen($json))
            ->setRawData($json)
            ->request('POST');
        
        $body = $httpResp->getBody();
        if ($httpResp->getHeader(\Zend_Http_Client::CONTENT_TYPE) == 'application/json') {
            $bodyArr = json_decode($body, true);
        }
        
        if ($httpResp->getStatus() == 200) {
            return isset($bodyArr)?$bodyArr:$body;
        } else {
            if (isset($bodyArr['error'])) {
                throw new  \Icare\Exception\Model\IcareException($bodyArr['error']['code'].': '.$bodyArr['error']['message']);
            } else {
                $errmsg = 'Unexpected status code: '.$httpResp->getStatus().' - '.$httpResp->getMessage();
                throw new \Magento\Framework\Exception\IntegrationException(new \Magento\Framework\Phrase($errmsg));
            }
        }
    }
    
    /**
     * @see \Magento\Framework\Oauth\Oauth::buildAuthorizationHeader()
     */
    public function buildAuthorizationHeader(
        $requestUrl,
        $signatureMethod = OAuthInterface::SIGNATURE_SHA256,
        $httpMethod = 'POST'
        ) {
        //$required = ["oauth_consumer_key", "oauth_consumer_secret", "oauth_token", "oauth_token_secret"];
            $params = array(
                "oauth_token" => $this->_netsuiteConf->getOauthToken(),
                "oauth_consumer_key" => $this->_netsuiteConf->getOauthConsumerKey(),
                'oauth_signature_method' => $signatureMethod,
                'oauth_nonce' => $this->_oauthHelper->generateRandomString(Oauth::LENGTH_NONCE),
                'oauth_timestamp' => $this->_date->timestamp(),
                'oauth_version' => '1.0',
            );
            
            // parse query params
            $queryParams = array();
        $parts = parse_url($requestUrl);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            $params = array_merge($queryParams, $params);
        }
            
        $params['oauth_signature'] = $this->_httpUtility->sign(
                $params,
                $signatureMethod,
                $this->_netsuiteConf->getOauthConsumerSecret(),
                $this->_netsuiteConf->getOauthTokenSecret(),
                $httpMethod,
                $requestUrl
                );
        $authorizationHeader = $this->_httpUtility->toAuthorizationHeader($params, $this->_netsuiteConf->getAccountId());
            // toAuthorizationHeader adds an optional realm="" which is not required for now.
            // http://tools.ietf.org/html/rfc2617#section-1.2
            return $authorizationHeader;
    }
}
