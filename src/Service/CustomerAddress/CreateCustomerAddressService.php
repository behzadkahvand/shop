<?php

namespace App\Service\CustomerAddress;

use App\DTO\Customer\CustomerAddressData;
use App\Entity\CustomerAddress;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CreateCustomerAddressService
{
    protected EntityManagerInterface $manager;

    protected CustomerAddressFactory $factory;

    protected DefaultCustomerAddressService $defaultCustomerAddress;

    public function __construct(
        EntityManagerInterface $manager,
        CustomerAddressFactory $factory,
        DefaultCustomerAddressService $defaultCustomerAddress
    ) {
        $this->manager = $manager;
        $this->factory = $factory;
        $this->defaultCustomerAddress = $defaultCustomerAddress;
    }

    public function create(CustomerAddressData $customerAddressData): CustomerAddress
    {
        $this->manager->beginTransaction();

        try {
            $customer = $customerAddressData->getCustomer();

            $customerAddress = $this->factory->getCustomerAddress();

            $customerAddress->setCustomer($customer)
                ->setFullAddress($customerAddressData->getFullAddress())
                ->setCoordinates($customerAddressData->getLocation())
                ->setProvince($customerAddressData->getProvince())
                ->setCity($customerAddressData->getCity())
                ->setPostalCode($customerAddressData->getPostalCode())
                ->setNumber($customerAddressData->getNumber())
                ->setUnit($customerAddressData->getUnit())
                ->setFloor($customerAddressData->getFloor())
            ;

            $district = $customerAddressData->getDistrict();

            if ($district) {
                $customerAddress->setDistrict($district);
            }

            if ($customerAddressData->isMyAddress()) {
                $customerAddress->setName($customer->getName())
                    ->setFamily($customer->getFamily())
                    ->setNationalCode($customer->getNationalNumber())
                    ->setMobile($customer->getMobile())
                    ->setIsForeigner($customer->getIsForeigner())
                    ->setPervasiveCode($customer->getPervasiveCode())
                ;
            } else {
                $customerAddress->setName($customerAddressData->getName())
                    ->setFamily($customerAddressData->getFamily())
                    ->setNationalCode($customerAddressData->getNationalCode())
                    ->setMobile($customerAddressData->getMobile())
                    ->setIsForeigner($customerAddressData->isForeigner())
                    ->setPervasiveCode($customerAddressData->getPervasiveCode())
                ;
            }

            $this->manager->persist($customerAddress);
            $this->manager->flush();

            $this->defaultCustomerAddress->set($customer, $customerAddress);

            $this->manager->commit();
        } catch (Exception $exception) {
            $this->manager->close();
            $this->manager->rollBack();

            throw $exception;
        }

        return $customerAddress;
    }
}
