<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Icare\Customer\Api\Data;

interface CustomerInfoInterface
{
    /**
     * @return string
     */
    public function getFullName();

    /**
     * @return string
     */
    public function getDob();

    /**
     * @return string
     */
    public function getWebsiteId();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getTelephone();

    /**
     * @return string
     */
    public function getOrganizationId();

    /**
     * @return string
     */
    public function getOrganizationName();

    /**
     * @return string
     */
    public function getEmployerId();

    /**
     * @return string
     */
    public function getSocialId();

    /**
     * @return string
     */
    public function getSocialType();

    /**
     * @return string
     */
    public function getCreditLimit();

    /**
     * @return string
     */
    public function getDueLimit();

    /**
     * @return string
     */
    public function getCompany();

    /**
     * @return string
     */
    public function getGender();
    /**
     * @return string
     */
    public function getStoreId();

    /**
     *
     * @param type $full_name
     *
     */
    public function setFullName($full_name);

    /**
     *
     * @param type $dob
     */
    public function setDob($dob);

    /**
     *
     * @param type $website_id
     */
    public function setWebsiteId($website_id);

    /**
     *
     * @param type $address
     */
    public function setAddress($address);

    /**
     *
     * @param type $postal_code
     */
    public function setPostalCode($postal_code);

    /**
     *
     * @param type $city
     */
    public function setCity($city);

    /**
     *
     * @param type $state
     */
    public function setState($state);

    /**
     *
     * @param type $country
     */
    public function setCountry($country);

    /**
     *
     * @param type $email
     */
    public function setEmail($email);

    /**
     *
     * @param type $telephone
     */
    public function setTelephone($telephone);

    /**
     *
     * @param type $organization_id
     */
    public function setOrganizationId($organization_id);

    /**
     *
     * @param type $organization_name
     */
    public function setOrganizationName($organization_name);

    /**
     *
     * @param type $employer_id
     */
    public function setEmployerId($employer_id);

    /**
     *
     * @param type $social_id
     */
    public function setSocialId($social_id);

    /**
     *
     * @param type $social_type
     */
    public function setSocialType($social_type);

    /**
     *
     * @param type $due_limit
     */
    public function setDueLimit($due_limit);

    /**
     *
     * @param type $credit_limit
     */
    public function setCreditLimit($credit_limit);

    /**
     *
     * @param type $company
     */
    public function setCompany($company);

    /**
     *
     * @param type $gender
     */
    public function setGender($gender);

    /**
     *
     * @param type $storeId
     */
    public function setStoreId($storeId);
    
    /**
     * @return string
     */
    public function getCustomerId();
    
    /**
     * 
     * @param string $customer_id
     */
    public function setCustomerId($customer_id);

    /**
     * getIsActive
     * @return string
     */
    public function getIsActive();
    
    /**
     * 
     * @param string $is_active
     */
    public function setIsActive($is_active);

}


