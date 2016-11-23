<?php 

namespace Icare\Variable\Api;

/**
 * interface providing access to variables value
 * @author Nam Pham
 *
 */
interface VariableInterface 
{
    /**
     * retrieve variable values (associative array with key value mapping)
     * @param string[] $names
     * @param string[] $optionals
     * @return mixed
     */
    public function retrieveVariables($names, $optionals = array());

    /**
     * @param string $code
     * @return mixed
     */
    public function getListByCode($code);
}