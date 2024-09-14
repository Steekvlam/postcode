<?php

namespace Ridders\Postcode\Api;

use Ridders\Postcode\Api\Data\AddressInterface;

interface ServiceInterface
{
    public function getAddress(AddressInterface $address): AddressInterface;
}
