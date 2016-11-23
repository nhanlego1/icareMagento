<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 26/09/2016
 * Time: 13:52
 */

namespace Icare\Custom\Api;


interface CustomApiInterface {

    /**
     * getSiteList
     * @description : If keyword is empty, no condition will be effected. Response
     * data will contains all websites and it owner stores
     * @param string $keyword
     * @return mixed
     */
    public function getSiteList($keyword = '');


    /**
     * @return mixed
     */
    public function getCountriesAndStates();
}