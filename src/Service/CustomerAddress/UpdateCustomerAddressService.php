<?php

namespace App\Service\CustomerAddress;

use App\DTO\Customer\CustomerAddressData;
use App\Entity\CustomerAddress;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UpdateCustomerAddressService
{
    protected EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function perform(CustomerAddress $customerAddress, CustomerAddressData $customerAddressData): CustomerAddress
    {
        $this->manager->beginTransaction();

        try {
            $customerAddress->setFullAddress($customerAddressData->getFullAddress())
                ->setCoordinates($customerAddressData->getLocation())
                ->setProvince($customerAddressData->getProvince())
                ->setCity($customerAddressData->getCity())
                ->setPostalCode($customerAddressData->getPostalCode())
                ->setNumber($customerAddressData->getNumber())
                ->setUnit($customerAddressData->getUnit())
                ->setFloor($customerAddressData->getFloor())
                ->setDistrict($customerAddressData->getDistrict());

            if ($customerAddressData->isMyAddress()) {
                $customer = $customerAddress->getCustomer();

                $customerAddress->setName($customer->getName())
                    ->setFamily($customer->getFamily())
                    ->setNationalCode($customer->getNationalNumber())
                    ->setMobile($customer->getMobile());
            } else {
                $customerAddress->setName($customerAddressData->getName())
                    ->setFamily($customerAddressData->getFamily())
                    ->setNationalCode($customerAddressData->getNationalCode())
                    ->setMobile($customerAddressData->getMobile());
            }

            $this->manager->flush();

            $this->manager->commit();
        } catch (Exception $exception) {
            $this->manager->close();
            $this->manager->rollback();

            throw $exception;
        }

        return $customerAddress;
    }
}
