<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 14/10/2016
 * Time: 11:21
 */
namespace Icare\MobileSecurity\Api;

use Icare\MobileSecurity\Model\MobileSecurityInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface MobileSecurityRepositoryInterface 
{
    public function save(MobileSecurityInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(MobileSecurityInterface $page);

    public function deleteById($id);
}
