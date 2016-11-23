<?php
namespace Icare\Variable\Model;

use Magento\Framework\Acl\AclResource\Provider;

/**
 * Override default AclResource provider to make each varialbes as a resource
 * 
 * @author Nam Pham
 *        
 */
class AclResourceProvider extends \Magento\Framework\Acl\AclResource\Provider
{
    private $_variables;

    /**
     *
     * @param \Magento\Framework\Config\ReaderInterface $configReader            
     * @param \Magento\Framework\Acl\AclResource\TreeBuilder $resourceTreeBuilder            
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $configReader, 
        \Magento\Framework\Acl\AclResource\TreeBuilder $resourceTreeBuilder,
        \Magento\Variable\Model\ResourceModel\Variable\Collection $variables)
    {
        parent::__construct($configReader, $resourceTreeBuilder);
        
        $this->_variables = $variables;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\Framework\Acl\AclResource\Provider::getAclResources()
     */
    public function getAclResources()
    {
        $resources = parent::getAclResources();
        
        $resource = null;
        $resource = &self::getHierarchicalResource($resources, array(
            "Icare_Variable::variable",
            "Icare_Api::Api",
            "Magento_Backend::admin",
        ));
        // add variables to resources
        $variables = $this->_variables->toOptionArray();
        $keyvalues = array();
        foreach ($variables as $variable) {
            $keyvalues[$variable['value']] = $variable['label'];
        }
        asort($keyvalues);
        foreach ($keyvalues as $key => $value) {
            $resource['children'][] = array(
                'id' => 'Icare_Variable::' . $key,
                'title' => $value,
                'sortOrder' => 0,
                'children' => array()
            );
        }
        
        return $resources;
    }
    
    /**
     * 
     * @param array $resources
     * @param array $hierarchy
     * @return array 
     */
    static private function &getHierarchicalResource(&$resources, $hierarchy) 
    {
        $current = &$resources;
        while (count($hierarchy) > 0) {
            $key = array_pop($hierarchy);
            unset($next);
            foreach ($current as &$item) {
                if ($item['id'] == $key) {
                    $next = &$item;
                    break;
                }
            }
            if (isset($next)) {
                if (empty($hierarchy)) {
                    $current = &$next;
                }
                else {
                    $current = &$next['children'];
                }
            }
            else {
                $current = FALSE;
                break;
            }
        }
        
        return $current;
    }
}