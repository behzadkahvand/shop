<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CustomerFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "customer_1",
            $this->createCustomer(
                'test',
                'test',
                'test@test.ir',
                'registered',
                '123456',
                'MALE',
                $this->faker->dateTime(),
                '09121234567',
                '0800008200',
                'wallet_1'
            )
        );
        $this->setReferenceAndPersist(
            "customer_2",
            $this->createCustomer(
                'test 2',
                'test 2',
                'test2@test.ir',
                'registered',
                '123456',
                'MALE',
                $this->faker->dateTime(),
                '09121234568',
                '0040100006',
                'wallet_2'
            )
        );
        $this->setReferenceAndPersist(
            "customer_3",
            $this->createCustomer(
                'test 3',
                'test 3',
                'test3@test.ir',
                'registered',
                '123456',
                'FEMALE',
                $this->faker->dateTime(),
                '09121234569',
                '0009000070',
                'wallet_3'
            )
        );
        $this->setReferenceAndPersist(
            "customer_4",
            $this->createCustomer(
                'مشتری',
                'بدون سفارش',
                'test4@test.ir',
                'registered',
                '123456',
                'FEMALE',
                $this->faker->dateTime(),
                '09121234570',
                '0009000071',
                'wallet_4'
            )
        );
        $this->setReferenceAndPersist(
            "customer_5",
            $this->createCustomer(
                'مشتری',
                'بدون سفارش دوم',
                'test5@test.ir',
                'registered',
                '123456',
                'FEMALE',
                $this->faker->dateTime(),
                '09121234572',
                '0009000072',
                'wallet_5'
            )
        );

        $this->manager->flush();
    }

    private function createCustomer(
        string $name,
        string $family,
        string $email,
        string $status,
        string $password,
        string $gender,
        DateTime $birthday,
        string $mobile,
        string $nationalNumber,
        string $wallet,
        bool $isActive = true
    ): Customer {
        $customer = (new Customer());

        $customer->setName($name)
                 ->setFamily($family)
                 ->setEmail($email)
                 ->setStatus($status)
                 ->setPassword($this->faker->encodePassword($customer, $password))
                 ->setGender($gender)
                 ->setBirthday($birthday)
                 ->setMobile($mobile)
                 ->setNationalNumber($nationalNumber)
                 ->setWallet($this->getReference($wallet))
                 ->setIsActive($isActive);

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            WalletFixtures::class
        ];
    }
}
