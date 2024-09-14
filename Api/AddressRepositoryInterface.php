<?php

declare(strict_types=1);

namespace Ridders\Postcode\Api;

use Magento\Framework\Webapi\Exception as WebapiException;
use Ridders\Postcode\Api\Data\AddressInterface;
use Ridders\Postcode\Exception\ApiDisabledException;

interface AddressRepositoryInterface
{
    /**
     * @param AddressInterface $address
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     * @throws WebapiException
     */
    public function lookup(AddressInterface $address): AddressInterface;

    /**
     * @param AddressInterface $address
     * @return \Ridders\Postcode\Api\Data\AddressInterface
     */
    public function getAddress(AddressInterface $address): AddressInterface;
}
