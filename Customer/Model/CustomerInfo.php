<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Icare\Customer\Model;

use Icare\Customer\Api\Data;


class CustomerInfo implements Data\CustomerInfoInterface
{
    private $full_name;
    private $dob;
    private $website_id;
    private $address;
    private $postal_code;
    private $city;
    private $state;
    private $country;
    private $email;
    private $telephone;
    private $organization_id;
    private $organization_name;
    private $employer_id;
    private $social_id;
    private $social_type;
    private $credit_limit;
    private $due_limit;
    private $company;
    private $gender;
    private $storeId;
    private $customer_id;
    private $is_active;
    
    public function getIsActive() {
        return $this->is_active;
    }
    
    public function setIsActive($is_active) {
        $this->is_active = $is_active;
    }
    
    public function getCustomerId() {
        return $this->customer_id;
    }
    
    public function setCustomerId($customer_id) {
        $this->customer_id = $customer_id;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getDob()
    {
        return $this->dob;
    }

    public function getWebsiteId()
    {
        return $this->website_id;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostalCode()
    {
        return $this->postal_code;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function getOrganizationId()
    {
        return $this->organization_id;
    }

    public function getOrganizationName()
    {
        return $this->organization_name;
    }

    public function getEmployerId()
    {
        return $this->employer_id;
    }

    public function getSocialId()
    {
        return $this->social_id;
    }

    public function getSocialType()
    {
        return $this->social_type;
    }

    public function getCreditLimit()
    {
        return $this->credit_limit;
    }

    public function getDueLimit()
    {
        return $this->due_limit;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }


    public function setFullName($full_name)
    {
        $this->full_name = $full_name;
    }

    public function setDob($dob)
    {
        $this->dob = $dob;
    }

    public function setWebsiteId($website_id)
    {
        $this->website_id = $website_id;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function setOrganizationId($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function setEmployerId($employer_id)
    {
        $this->employer_id = $employer_id;
    }

    public function setSocialId($social_id)
    {
        $this->social_id = $social_id;
    }

    public function setSocialType($social_type)
    {
        $this->social_type = $social_type;
    }

    public function setOrganizationName($organization_name)
    {
        $this->organization_name = $organization_name;
    }

    public function setCreditLimit($credit_limit)
    {
        $this->credit_limit = $credit_limit;
    }

    public function setDueLimit($due_limit)
    {
        $this->due_limit = $due_limit;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

}

?>