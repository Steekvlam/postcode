<?php
declare(strict_types=1);

namespace Ridders\Postcode\Api\Data;

interface AddressInterface
{
    const STATUS                = 'status';
    const POSTCODE              = 'postcode';
    const COUNTRY               = 'country';
    const PROVINCE              = 'province';
    const CITY                  = 'city';
    const STREET                = 'street';
    const HOUSE_NUMBER          = 'house_number';
    const HOUSE_NUMBER_ADDITION = 'number_addition';

    /**
     * @return string
     */
    public function getPostcode(): string;

    /**
     * @param string $postcode
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setPostcode(string $postcode): AddressInterface;

    /**
     * @return string
     */
    public function getCountry(): string;

    /**
     * @param string $country
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setCountry(string $country): AddressInterface;

    /**
     * @return string
     */
    public function getProvince(): string;

    /**
     * @param string $province
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setProvince(string $province): AddressInterface;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setCity(string $city): AddressInterface;

    /**
     * @return string
     */
    public function getStreet(): string;

    /**
     * @param string $street
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setStreet(string $street): AddressInterface;

    /**
     * @return int
     */
    public function getHouseNumber(): int;

    /**
     * @param int $houseNumber
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setHouseNumber(int $houseNumber): AddressInterface;

    /**
     * @return string
     */
    public function getHouseNumberAddition(): string;

    /**
     * @param string $addition
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function setHouseNumberAddition(string $addition): AddressInterface;
}
