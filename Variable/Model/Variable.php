<?php 

namespace Icare\Variable\Model;

use Magento\Framework\Webapi\Exception as WebApiException;

/**
 * 
 * @author Nam Pham
 *
 */
class Variable implements \Icare\Variable\Api\VariableInterface
{
    private $_authorization;
    
    private $_variables;
    
    /**
     * 
     * @param \Magento\Variable\Model\ResourceModel\Variable\Collection $variables
     * @param \Magento\User\Model\User $user
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     */
    public function __construct(
        \Magento\Variable\Model\ResourceModel\Variable\Collection $variables,
        \Magento\User\Model\User $user,
        \Magento\Framework\AuthorizationInterface $authorization) 
    {
        $this->_variables = $variables;
        $this->_authorization = $authorization;
    }
    
    
    /**
     *
     * {@inheritDoc}
     * @see \Icare\Variable\Api\VariableInterface::retrieveVariables()
     */
    public function retrieveVariables($names, $optionals = array())
    {
        foreach ($names as $name) {
            if (!$this->_authorization->isAllowed('Icare_Variable::'.$name)) {
                throw new WebApiException(__('Access to resource is denied: '.$name), WebApiException::HTTP_FORBIDDEN, WebApiException::HTTP_FORBIDDEN);
            }
        }
    
        $values = array();
        foreach ($this->_variables as $variable) {
            if (in_array($variable->getCode(), $names)) {
                $values[$variable->getCode()] = $variable->loadByCode($variable->getCode())->getPlainValue();
            }
        }
    
        // verify if all required variables are retrieved
        foreach ($names as $name) {
            if (!isset($values[$name]) && !in_array($name, $optionals)) {
                $missing[] = $name;
            }
        }
        if (!empty($missing)) {
            throw new WebApiException(__('Variable not found'), WebApiException::HTTP_NOT_FOUND, WebApiException::HTTP_NOT_FOUND, $missing);
        }
        return [$values];
    }

    /**
     * @see \Icare\Variable\Api\VariableInterface::getListByCode()
     */
    public function getListByCode($code)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $variable = $om->create('Magento\Variable\Model\Variable')->loadByCode($code);

        return [$variable->getData()];
    }
}