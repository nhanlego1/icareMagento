<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 20/07/2016
 * Time: 10:28
 */

namespace Icare\Exception\Model;


use Magento\Framework\Exception\LocalizedException;

class IcareException extends LocalizedException
{
    /**
     * IcareException constructor.
     * @param string $errorMessage
     * @param \Exception|null $cause
     */
    public function __construct($errorMessage, \Exception $cause = null)
    {
        parent::__construct(new \Magento\Framework\Phrase($errorMessage), $cause);
    }
}