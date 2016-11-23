<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 26/09/2016
 * Time: 13:53
 */

namespace Icare\Custom\Model;


use Icare\Custom\Api\CustomApiInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Magento\Framework\App\ObjectManager;

class CustomApiHandler implements CustomApiInterface{
    

    /**
     * getSiteList
     * @description : If keyword is empty, no condition will be effected. Response
     * data will contains all websites and it owner stores
     * @param string $keyword
     * @return mixed
     */
    public function getSiteList($keyword = '') {
        $objectManager = ObjectManager::getInstance();
        /**
         * @var \Magento\Store\Model\ResourceModel\Website\Collection $collection
         */
        $collection = $objectManager->create('\Magento\Store\Model\ResourceModel\Website\Collection');
        if(!empty($keyword)){
            $collection->addFieldToFilter([
                'website_id'=>'website_id',
                'code' => 'code',
                'name' => 'name'
            ],[
                'website_id'=>[
                    'like' =>$keyword
                ],
                'code'=>[
                    'like' => "%{$keyword}%"
                ],
                'name' => [
                    'like' => "%{$keyword}%"
                ]
            ]);
        }
        $collection->addFieldToSelect('*');
        $sites = $collection->getData();
        /**
         * Get all website's stores
         */
        foreach ($sites as &$site){
            /**
             * @var \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
             */
            $storeCollection = $objectManager->create('\Magento\Store\Model\ResourceModel\Store\Collection');
            $storeCollection->addFieldToFilter('website_id',$site['website_id']);
            $site['stores'] = $storeCollection->getData();
        }
        /**
         * Response data
         */
        return $sites;
    }

    public function getCountriesAndStates() {
        $countries = [];
        try {
            $om = ObjectManager::getInstance();
            $countryHelper = $om->get('Magento\Directory\Model\Config\Source\Country');
            $countryFactory = $om->get('Magento\Directory\Model\CountryFactory');
            $countryCollection = $om->get('Magento\Directory\Model\ResourceModel\Country\Collection');

            $countries = $countryHelper->toOptionArray(); //Load an array of countries
            foreach ( $countries as $countryKey => $country ) {


                if ( $country['value'] != '' ) { //Ignore the first (empty) value

                    $stateArray = $countryFactory->create()->setId(
                        $country['value']
                    )->getLoadedRegionCollection()->toOptionArray(); //Get all regions for the given ISO country code

                    if ( count($stateArray) > 0 ) { //Again ignore empty values
                        $countries[$countryKey]['states'] = $stateArray;
                    }

                }
            }
        } catch (\Exception $ex) {
            $result[] = new IcareException($ex->getMessage());
            throw new IcareWebApiException(402, __('Web Api Internal Error'), $result);
        }
        return $countries;
    }
}