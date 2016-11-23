<?php
/**
 * Created by PhpStorm.
 * User: baonq
 * Date: 14/07/2016
 * Time: 10:40
 */

namespace Icare\IcareOrderApi\Model;

use Icare\IcareOrderApi\Api\Data\AddressValueInterface;

class AddressValue implements AddressValueInterface
{
    private $street;
    private $city;
    private $postcode;
    private $telephone;
    private $country;
    private $district;

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = isset($street) ? $street : null;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = isset($city) ? $city : null;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function setPostcode($postcode)
    {
        $this->postcode = isset($postcode) ? $postcode : null;
    }

    public function getCountry()
    {
       return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = isset($country) ? $country : null;
    }

    public function getTelephone()
    {
       return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = isset($telephone) ? $telephone : null;
    }

    public function getDistrict()
    {
        return $this->district;
    }

    public function setDistrict($district)
    {
        $this->district = isset($district) ? $district : null;
    }
}