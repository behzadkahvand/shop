<?php

namespace App\Service\CustomerLegalAccount;

use App\DTO\Customer\CustomerLegalAccountData;
use App\Entity\CustomerLegalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CustomerLegalAccountService
{
    protected EntityManagerInterface $manager;

    protected CustomerLegalAccountFactory $factory;

    public function __construct(
        EntityManagerInterface $manager,
        CustomerLegalAccountFactory $factory
    ) {
        $this->manager = $manager;
        $this->factory = $factory;
    }

    public function store(CustomerLegalAccountData $customerLegalAccountData): CustomerLegalAccount
    {
        $this->manager->beginTransaction();

        try {
            $customer = $customerLegalAccountData->getCustomer();

            $legalAccount = $customer->getLegalAccount();

            if (!$legalAccount) {
                $legalAccount = $this->factory->getCustomerLegalAccount();

                $legalAccount->setCustomer($customer);
            }

            $legalAccount->setProvince($customerLegalAccountData->getProvince())
                         ->setCity($customerLegalAccountData->getCity())
                         ->setOrganizationName($customerLegalAccountData->getOrganizationName())
                         ->setEconomicCode($customerLegalAccountData->getEconomicCode())
                         ->setNationalId($customerLegalAccountData->getNationalId())
                         ->setRegistrationId($customerLegalAccountData->getRegistrationId())
                         ->setPhoneNumber($customerLegalAccountData->getPhoneNumber());

            $this->manager->persist($legalAccount);
            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollBack();

            throw $e;
        }

        return $legalAccount;
    }
}
