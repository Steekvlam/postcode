<?php

namespace Ridders\Postcode\Service;

use GuzzleHttp\ClientFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Psr\Log\LoggerInterface;
use Ridders\Postcode\Api\Data\AddressInterface;
use Ridders\Postcode\Api\Data\AddressInterfaceFactory;
use Ridders\Postcode\Exception\InvalidCountryException;
use Ridders\Postcode\Exception\ValidationException;

abstract class AbstractService
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * validation parameters per country
     *
     * @var array
     */
    protected $addressValidation = [
        // 'COUNTRY_CODE' => [
        //     'FIELD_NAME' => [\Laminas\Validator\NotEmpty::class, ['option' => value] // optional]
        // ]
    ];

    /**
     * AbstractService constructor.
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonSerializer $serializer
     * @param ClientFactory $clientFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        JsonSerializer $serializer,
        ClientFactory $clientFactory,
        AddressInterfaceFactory $addressFactory,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Validate the address
     *
     * @param AddressInterface $address
     *
     * @throws ValidationException
     * @throws InvalidCountryException
     */
    protected function validateAddress(AddressInterface $address)
    {
        $address = $address->__toArray();

        $validator = new \Laminas\Validator\NotEmpty();
        if (!array_key_exists(AddressInterface::COUNTRY, $address) || !$validator->isValid($address[AddressInterface::COUNTRY])) {
            throw new ValidationException('Missing required parameter country');
        }

        $country = $address[AddressInterface::COUNTRY];

        if (!isset($this->addressValidation[$country])) {
            throw new InvalidCountryException(sprintf('Invalid country "%s"', $country));
        }

        foreach ($this->addressValidation[$country] as $field => $validation) {
            $validationOptions = isset($validation[1]) && is_array($validation[1]) ? $validation[1] : null;
            $validatorClass = $validation[0];

            $validator = new $validatorClass($validationOptions);
            if (!array_key_exists($field, $address) || !$validator->isValid($address[$field])) {
                throw new ValidationException(sprintf('"%s" validation failed', $field));
            }
        }
    }
}
