<?php

namespace App\DataFixtures;

use App\Entity\OrderLegalAccount;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderLegalAccountFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'order_legal_account_1',
            $this->createOrderLegalAccount(
                'order_4',
                'customer_legal_account_1',
                'province_1',
                'city_1',
                'Lendo cooperation',
                '100000045620000',
                '8915413244',
                '591598577545',
                '02188205401',
                true
            )
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            CityFixtures::class,
            ProvinceFixtures::class,
            CustomerLegalAccountFixtures::class,
        ];
    }

    private function createOrderLegalAccount(
        string $orderName,
        string $customerLegalAccountName,
        string $provinceName,
        string $cityName,
        string $organizationName,
        string $economicCode,
        string $nationalId,
        string $registrationId,
        string $phoneNumber,
        bool $isActive,
    ): OrderLegalAccount {
        return (new OrderLegalAccount())
            ->setOrder($this->getReference($orderName))
            ->setCustomerLegalAccount($this->getReference($customerLegalAccountName))
            ->setProvince($this->getReference($provinceName))
            ->setCity($this->getReference($cityName))
            ->setOrganizationName($organizationName)
            ->setEconomicCode($economicCode)
            ->setNationalId($nationalId)
            ->setRegistrationId($registrationId)
            ->setPhoneNumber($phoneNumber)
            ->setIsActive($isActive);
    }
}
