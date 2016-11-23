<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/4/16
 * Time: 2:57 PM
 */

namespace Icare\Customer\Ui\Component;


class CustomerDataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    protected function prepareUpdateUrl()
    {
        $this->addFilter(
            $this->filterBuilder->setField('customer.icare_center_type')->setValue([1,2])->setConditionType('nin')->create()
        );
        return parent::prepareUpdateUrl();
    }

}