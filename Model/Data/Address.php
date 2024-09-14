<?php


namespace Ridders\Postcode\Model\Data;


use Magento\Framework\Api\AbstractSimpleObject;
use Ridders\Postcode\Api\Data\AddressInterface;

class Address extends AbstractSimpleObject implements AddressInterface
{
    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->_get(self::POSTCODE) ?? '';
    }

    /**
     * @param string $postcode
     * @return AddressInterface
     */
    public function setPostcode(string $postcode): AddressInterface
    {
        $postcode = preg_replace('~[^A-Z0-9]+~', '', strtoupper($postcode));
        return $this->setData(self::POSTCODE, (string)$postcode);
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->_get(self::COUNTRY) ?? '';
    }

    /**
     * @param string $country
     * @return AddressInterface
     */
    public function setCountry(string $country): AddressInterface
    {
        return $this->setData(self::COUNTRY, (string)$country);
    }

    /**
     * @return string
     */
    public function getProvince(): string
    {
        return $this->_get(self::PROVINCE) ?? '';
    }

    /**
     * @param string $province
     * @return AddressInterface
     */
    public function setProvince(string $province): AddressInterface
    {
        return $this->setData(self::PROVINCE, (string)$province);
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->_get(self::CITY) ?? '';
    }

    /**
     * @param string $city
     * @return AddressInterface
     */
    public function setCity(string $city): AddressInterface
    {
        return $this->setData(self::CITY, (string)$city);
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->_get(self::STREET) ?? '';
    }

    /**
     * @param string $street
     * @return AddressInterface
     */
    public function setStreet(string $street): AddressInterface
    {
        return $this->setData(self::STREET, (string)$street);
    }

    /**
     * @return int
     */
    public function getHouseNumber(): int
    {
        return $this->_get(self::HOUSE_NUMBER);
    }

    /**
     * @param int $houseNumber
     * @return AddressInterface
     */
    public function setHouseNumber(int $houseNumber): AddressInterface
    {
        return $this->setData(self::HOUSE_NUMBER, (int)$houseNumber);
    }

    /**
     * @return string
     */
    public function getHouseNumberAddition(): string
    {
        return $this->_get(self::HOUSE_NUMBER_ADDITION) ?? '';
    }

    /**
     * @param string $addition
     * @return AddressInterface
     */
    public function setHouseNumberAddition(string $addition): AddressInterface
    {
        return $this->setData(self::HOUSE_NUMBER_ADDITION, (string)$addition);
    }
}
