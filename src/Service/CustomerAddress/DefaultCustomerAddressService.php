<?php

namespace App\Service\CustomerAddress;

use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Service\CustomerAddress\Exceptions\UnexpectedCustomerAddressException;
use Doctrine\ORM\EntityManagerInterface;

class DefaultCustomerAddressService
{
    protected EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @throws UnexpectedCustomerAddressException
     */
    public function set(Customer $customer, CustomerAddress $defaultAddress, bool $performFlush = true): void
    {
        foreach ($customer->getAddresses() as $address) {
            $address->setIsDefault(false);
        }

        if ($customer->getId() !== $defaultAddress->getCustomer()->getId()) {
            throw new UnexpectedCustomerAddressException();
        }

        $defaultAddress->setIsDefault(true);

        if ($performFlush) {
            $this->manager->flush();
        }
    }
}
