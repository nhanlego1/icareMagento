<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 26/10/2016
 * Time: 10:00
 */

namespace Icare\Sales\Ui\Component;


class XMLValidator implements \Magento\Framework\Config\ValidationStateInterface {
    /**
         * Retrieve current validation state
         *
         * @return boolean
         */
    public function isValidationRequired() {
                // TODO: Implement isValidationRequired() method.
                return false;
    }
}