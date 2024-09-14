<?php

namespace Ridders\Postcode\Service;

use GuzzleHttp\Exception\GuzzleException;
use Ridders\Postcode\Api\Data\AddressInterface;
use Ridders\Postcode\Api\ServiceInterface;
use Ridders\Postcode\Exception\ApiDisabledException;
use Ridders\Postcode\Exception\UndefinedException;

class RiddersApi extends AbstractService implements ServiceInterface
{
    const BASE_URI = 'https://postcode.ridders.nl/';

    protected $addressValidation = [
        'NL' => [
            AddressInterface::POSTCODE => [\Laminas\Validator\NotEmpty::class],
            AddressInterface::HOUSE_NUMBER => [\Laminas\Validator\NotEmpty::class],
        ],
    ];

    /**
     * @return string
     * @throws ApiDisabledException
     */
    protected function getApiKey(): string
    {
        $apiKey = $this->scopeConfig->getValue('ridders_postcode/general/ridders_api/api_key');
        if (!$apiKey) {
            throw new ApiDisabledException("API key missing");
        }

        return $apiKey;
    }

    /**
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws ApiDisabledException
     * @throws AddressInvalidException
     * @throws AddressNotFoundException
     * @throws UndefinedException
     * @throws InvalidCountryException
     * @throws ValidationException
     */
    public function getAddress(AddressInterface $address): AddressInterface
    {
        $this->validateAddress($address);

        $postcode = $address->getPostcode();
        $houseNumber = $address->getHouseNumber();

        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => self::BASE_URI,
            ],
        ]);

        try {
            $response = $client->request(
                \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                '',
                [
                    'query' => [
                        'apikey' => $this->getApiKey(),
                        'postcode' => $postcode,
                        'nummer' => $houseNumber,
                    ],
                ],
            );
        } catch (GuzzleException $exception) {
            throw new UndefinedException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $content = $this->serializer->unserialize($responseContent);

        if (isset($content['errorCode'])) {
            switch ($content['errorCode']) {
                default:
                    $this->logger->error($responseContent);
                    throw new UndefinedException('Undefined exception');
                case 100:
                    throw new \Ridders\Postcode\Exception\AddressInvalidException('Invalid or missing postcode');
                case 102:
                    throw new \Ridders\Postcode\Exception\AddressInvalidException('Invalid or missing house number');
                case 300:
                    throw new \Ridders\Postcode\Exception\AddressNotFoundException('Address not found');
            }
        }

        $address
            ->setStreet($content['street'])
            ->setCity($content['city'])
            ->setCountry('NL');

        return $address;
    }
}
