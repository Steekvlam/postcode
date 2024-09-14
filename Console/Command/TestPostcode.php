<?php

namespace Ridders\Postcode\Console\Command;

use Ridders\Postcode\Api\Data\AddressInterfaceFactory;
use Ridders\Postcode\Model\AddressService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class TestPostcode extends Command
{
    const ARGUMENT_POSTCODE = 'postcode';
    const ARGUMENT_NUMBER   = 'number';
    const ARGUMENT_COUNTRY  = 'country_code';

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * TestPostcode constructor.
     *
     * @param AddressService $addressService
     * @param AddressInterfaceFactory $addressFactory
     * @param null $name
     */
    public function __construct(
        AddressService $addressService,
        AddressInterfaceFactory $addressFactory,
        $name = null
    ) {
        parent::__construct($name);

        $this->addressService = $addressService;
        $this->addressFactory = $addressFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("ridders:postcode:test-postcode");
        $this->setDescription("Test postcode api");

        $this->addArgument(
            self::ARGUMENT_POSTCODE,
            InputArgument::REQUIRED,
            'Postcode'
        );

        $this->addArgument(
            self::ARGUMENT_NUMBER,
            InputArgument::REQUIRED,
            'House number'
        );

        $this->addArgument(
            self::ARGUMENT_COUNTRY,
            InputArgument::REQUIRED,
            'Country'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        $postcode = $input->getArgument(self::ARGUMENT_POSTCODE);
        $number = $input->getArgument(self::ARGUMENT_NUMBER);
        $country = $input->getArgument(self::ARGUMENT_COUNTRY);

        $address = $this->addressFactory->create();
        $address->setPostcode($postcode)
            ->setHouseNumber($number)
            ->setCountry($country);

        $start = microtime(true);
        $address = $this->addressService->getAddress($address);
        $requestTime = ceil((microtime(true) - $start) * 1000);

        print_r($address->__toArray());
        $output->writeln('Request completed in ' . $requestTime . ' ms');
    }
}
