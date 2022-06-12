<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CustomerAccountFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "account_1",
            $this->createCustomerAccount(
                'customer_1',
                '6364141910111896',
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                "IR" . $this->faker->randomNumber(),
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            "account_2",
            $this->createCustomerAccount(
                'customer_2',
                '6362141110111193',
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                "IR" . $this->faker->randomNumber(),
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            "account_3",
            $this->createCustomerAccount(
                'customer_3',
                '6362141110111896',
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                "IR" . $this->faker->randomNumber(),
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            "account_4",
            $this->createCustomerAccount(
                'customer_4',
                $this->faker->randomNumber(),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                "IR" . $this->faker->randomNumber(),
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            "account_5",
            $this->createCustomerAccount(
                'customer_5',
                $this->faker->randomNumber(),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                "IR" . $this->faker->randomNumber(),
                $this->faker->sentence(2)
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
            CustomerFixtures::class,
        ];
    }

    private function createCustomerAccount(
        string $customer,
        string $cardNumber,
        string $firstName,
        string $lastName,
        string $shebaNumber,
        string $bank
    ): Account {
        return (new Account())
            ->setCustomer($this->getReference($customer))
            ->setCardNumber($cardNumber)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setShebaNumber($shebaNumber)
            ->setBank($bank);
    }
}
