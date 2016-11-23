<?php
namespace Icare\Custom\Controller;

use Icare\Exception\Model\IcareWebApiException;
use Icare\MobileSecurity\Api\MobileSecurityBaseApi;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Webapi\Controller\PathProcessor;
use Icare\Exception\Model\IcareException;

class Rest extends \Magento\Webapi\Controller\Rest
{
    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        RestRequest $request,
        RestResponse $response,
        Router $router,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $appState,
        AuthorizationInterface $authorization,
        ServiceInputProcessor $serviceInputProcessor,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList,
        FieldsFilter $fieldsFilter,
        ParamsOverrider $paramsOverrider,
        ServiceOutputProcessor $serviceOutputProcessor,
        Generator $swaggerGenerator,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        parent::__construct($request, $response, $router, $objectManager, $appState, $authorization, $serviceInputProcessor, $errorProcessor, $pathProcessor
            , $areaList, $fieldsFilter, $paramsOverrider, $serviceOutputProcessor, $swaggerGenerator, $storeManager);

    }

    protected function processApiRequest()
    {
        $icareHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Icare\Custom\Helper\ICareHelper');
        try {
            $this->validateRequest();
            /** @var array $inputData */
            $inputData = $this->_request->getRequestData();
            $route = $this->getCurrentRoute();
            $serviceMethodName = $route->getServiceMethod();
            $serviceClassName = $route->getServiceClass();
            $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
            $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
            $service = $this->_objectManager->get($serviceClassName);
            /** @var \Magento\Framework\Api\AbstractExtensibleObject $outputData */
            $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
            $outputData = $this->serviceOutputProcessor->process(
                $outputData,
                $serviceClassName,
                $serviceMethodName
            );
            if ($this->_request->getParam(FieldsFilter::FILTER_PARAMETER) && is_array($outputData)) {
                $outputData = $this->fieldsFilter->filter($outputData);
            }
            $icareHelper->logRequest($this->_request, json_encode($outputData));
            $this->_response->prepareResponse($outputData);
        } catch (IcareWebApiException $ex) {
            $errors = $ex->getDetails();
            $messages = array();
            if ($errors) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
            }
            $icareHelper->logRequest($this->_request, json_encode($messages), true);
            throw $ex;
        }

    }

    /**
     * validateRequest
     * @throws \Icare\Exception\Model\IcareWebApiException
     * @throws \Magento\Framework\Webapi\Exception
     */
    protected function validateRequest()
    {

        $exceptMethods = [
            'Icare\Customer\Api\CustomerInterface::getListCustomer',
            'Icare\Customer\Api\CustomerInterface::loginBySocialId',
            'Magento\Store\Api\WebsiteRepositoryInterface::getList',
            'Magento\Integration\Api\AdminTokenServiceInterface::createAdminAccessToken',
            'Magento\Integration\Api\CustomerTokenServiceInterface::createCustomerAccessToken',
            'Icare\MobileSecurity\Api\AuthenticateInterface::authenticate',
            'Icare\MobileSecurity\Api\RegisterInterface::register',
            'Icare\Variable\Api\VariableInterface::getListByCode'
        ];

        $route = $this->getCurrentRoute();
        $appType = $this->_request->getHeader('app-type');
        $serviceClass = $route->getServiceClass();
        $serviceMethod = $route->getServiceMethod();
        if(!in_array($serviceClass.'::'.$serviceMethod,$exceptMethods) && $appType == MobileSecurityBaseApi::APP_ICARE){
            //check access token before continue
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $iCareHelper = $om->get('Icare\Custom\Helper\ICareHelper');
            $token = $this->_request->getHeader('iCare-Token');
            if (!$iCareHelper->validateCustomerToken(false, $token)) {
                throw new IcareWebApiException(403, __('Access token invalid. Plase try again.'));
            }
        }

        parent::validateRequest();
        
    }
}