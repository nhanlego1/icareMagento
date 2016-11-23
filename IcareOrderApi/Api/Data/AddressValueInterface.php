<?php

namespace Icare\IcareOrderApi\Api\Data;

interface AddressValueInterface
{
	/**
	 * @return string
	 */
	public function getStreet();

	/**
	 * @param string $street
	 */
	public function setStreet($street);

	/**
	 * @return string
	 */
	public function getCity();
	
	/**
	 * @param string $city
	 */
	public function setCity($city);

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $country
     */
    public function setCountry($country);
    /**
     * @return string
     */
    public function getTelephone();

    /**
     * @param string $telephone
     */
    public function setTelephone($telephone);
    /**
     * @return string
     */
    public function getDistrict();

    /**
     * @param string $district
     */
    public function setDistrict($district);
}

