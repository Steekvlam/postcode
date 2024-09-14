<?php
declare(strict_types=1);

namespace Ridders\Postcode\Model;

use Exception;
use Magento\Framework\Webapi\Exception as WebapiException;
use Ridders\Postcode\Api\AddressRepositoryInterface;
use Ridders\Postcode\Api\Data\AddressInterface;
use Ridders\Postcode\Exception\AddressInvalidException;
use Ridders\Postcode\Exception\AddressNotFoundException;
use Ridders\Postcode\Exception\ApiDisabledException;
use Ridders\Postcode\Exception\InvalidCountryException;
use Ridders\Postcode\Exception\UndefinedException;
use Ridders\Postcode\Exception\ValidationException;

class AddressRepository implements AddressRepositoryInterface
{
    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * AddressRepository constructor.
     *
     * @param AddressService $addressService
     */
    public function __construct(
        AddressService $addressService
    ) {
        $this->addressService = $addressService;
    }

    /**
     * possible http code: 200, 400, 404, 422, 500, 503
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws WebapiException
     */
    public function lookup(AddressInterface $address): AddressInterface
    {
        try {
            return $this->getAddress($address);
        } catch (AddressInvalidException | ValidationException $exception) {
            throw new WebapiException(
                __($exception->getMessage()),
                $exception->getCode(),
                WebapiException::HTTP_BAD_REQUEST
            );
        } catch (AddressNotFoundException $exception) {
            throw new WebapiException(
                __($exception->getMessage()),
                $exception->getCode(),
                WebapiException::HTTP_NOT_FOUND
            );
        } catch (InvalidCountryException $exception) {
            throw new WebapiException(
                __($exception->getMessage()),
                $exception->getCode(),
                422 // Unprocessable Entity
            );
        } catch (ApiDisabledException $exception) {
            throw new WebapiException(
                __($exception->getMessage()),
                $exception->getCode(),
                503 // service unavailable
            );
        } catch (Exception | UndefinedException $exception) {
            throw new WebapiException(
                __($exception->getMessage()),
                $exception->getCode(),
                WebapiException::HTTP_INTERNAL_ERROR
            );
        }
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
        return $this->addressService->getAddress($address);
    }
}

