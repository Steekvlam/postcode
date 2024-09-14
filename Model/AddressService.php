<?php

namespace Ridders\Postcode\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Ridders\Postcode\Api\Data\AddressInterface;
use Ridders\Postcode\Api\Data\AddressInterfaceFactory;
use Ridders\Postcode\Api\ServiceInterface;
use Ridders\Postcode\Exception\ApiDisabledException;

/**
 * Class AddressService
 *
 * @package Ridders\Postcode\Model
 */
class AddressService
{
    const XML_PATH_GENERAL_IS_ACTIVE                    = 'ridders_postcode/general/is_active';
    const XML_PATH_GENERAL_USE_MOCKUP_DATA              = 'ridders_postcode/general/use_mockup_data';
    const XML_PATH_GENERAL_MOCKUP_STATUS                = 'ridders_postcode/general/mockup/status';
    const XML_PATH_GENERAL_MOCKUP_COUNTRY_CODE          = 'ridders_postcode/general/mockup/country_code';
    const XML_PATH_GENERAL_MOCKUP_POSTCODE              = 'ridders_postcode/general/mockup/postcode';
    const XML_PATH_GENERAL_MOCKUP_PROVINCE              = 'ridders_postcode/general/mockup/province';
    const XML_PATH_GENERAL_MOCKUP_CITY                  = 'ridders_postcode/general/mockup/city';
    const XML_PATH_GENERAL_MOCKUP_STREET                = 'ridders_postcode/general/mockup/street';
    const XML_PATH_GENERAL_MOCKUP_HOUSE_NUMBER          = 'ridders_postcode/general/mockup/house_number';
    const XML_PATH_GENERAL_MOCKUP_HOUSE_NUMBER_ADDITION = 'ridders_postcode/general/mockup/house_number_addition';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ServiceInterface
     */
    protected $service;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * AddressService constructor.
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param ServiceInterface $service
     * @param AddressInterfaceFactory $addressFactory
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ServiceInterface $service,
        AddressInterfaceFactory $addressFactory,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->service = $service;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @return bool
     */
    public function addressServiceIsActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_IS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function addressServiceUseMockData(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_USE_MOCKUP_DATA);
    }

    /**
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws ApiDisabledException
     */
    public function getAddress(AddressInterface $address): AddressInterface
    {
        if ($this->addressServiceIsActive()) {
            return $this->service->getAddress($address);
        }

        if ($this->addressServiceUseMockData()) {
            return $this->getMockupAddress();
        }

        throw new ApiDisabledException('Address API disabled');
    }

    /**
     * @return AddressInterface
     */
    protected function getMockupAddress(): AddressInterface
    {
        $address = $this->addressFactory->create();
        $address->setPostcode($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_POSTCODE))
            ->setStreet($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_STREET))
            ->setHouseNumber($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_HOUSE_NUMBER))
            ->setHouseNumberAddition($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_HOUSE_NUMBER_ADDITION))
            ->setCity($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_CITY))
            ->setProvince($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_PROVINCE))
            ->setCountry($this->scopeConfig->getValue(self::XML_PATH_GENERAL_MOCKUP_COUNTRY_CODE));

        return $address;
    }
}
