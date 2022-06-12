<?php

namespace App\DataFixtures;

use App\Entity\CustomerLegalAccount;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CustomerLegalAccountFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $customerLegalAccount = (new CustomerLegalAccount())
            ->setCustomer($this->getReference('customer_1'))
            ->setProvince($this->getReference('province_tehran'))
            ->setCity($this->getReference('city_tehran'))
            ->setOrganizationName('Lendo cooperation')
            ->setEconomicCode(100000045620000)
            ->setNationalId('8915413244')
            ->setRegistrationId('591598577545')
            ->setPhoneNumber('02188205401');

        $this->addReference('customer_legal_account_1', $customerLegalAccount);

        $this->manager->persist($customerLegalAccount);
        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
            ProvinceFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
