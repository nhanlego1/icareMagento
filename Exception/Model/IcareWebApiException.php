<?php
/**
 * Icare Webapi module exception. Should be used in Icare web API services implementation.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Exception\Model;

use Magento\Framework\Webapi\Exception as WebApiException;

class IcareWebApiException extends WebApiException
{
    /**#@+
     * Error  Icare Web HTTP response code.
     * Example: const ICARE_BAD_PASSWORD = xxx;
     */

    /**
     * IcareWebApiException constructor.
     * @param int $code error code
     * @param string $name
     * @param \Magento\Framework\Exception\IcareException[]|null $errors Array of errors messages
     */
    public function __construct(
        $code = 0,
        $name = '',
        $errors = array()
    ) {
        if ($errors == null || count($errors) <= 0) {
            $errors = array();
            $errors[] = new IcareException($name);
            
        }
        parent::__construct(new \Magento\Framework\Phrase("iCare Web Api Error"), $code, parent::HTTP_BAD_REQUEST, array(), null, $errors);
    }

}
