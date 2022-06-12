<?php

namespace App\DataFixtures;

use App\Dictionary\TransactionStatus;
use App\Entity\Transaction;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TransactionFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'transaction_1',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                290_000,
                'Vandar',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_1',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_2',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                600_000,
                'Vandar',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_2',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_3',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                590_000,
                'Parsian',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_3',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_4',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                600_000,
                'CPG',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_4',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_5',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                349_000,
                'Irankish',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_5',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_6',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                680_000,
                'Saman',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_6',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_7',
            $this->createTransaction(
                TransactionStatus::NEW,
                245_000,
                'Saman',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_7',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_8',
            $this->createTransaction(
                TransactionStatus::PENDING,
                275_000,
                'Irankish',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_8',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_9',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(),
                'Vandar',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_7',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_10',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(),
                'Parsian',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_8',
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_11',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(),
                'CPG',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_8',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
            )
        );

        $this->setReferenceAndPersist(
            'transaction_12',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(),
                'Mellat',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'order_document_8',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
            )
        );

        $this->setReferenceAndPersist(
            'transaction_13',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Sadad',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_14',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Sadad',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_15',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Sadad',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_16',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'HamrahCard',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_17',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Zibal',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_18',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Saman',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_19',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Vandar',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_20',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'EFarda',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_8',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_21',
            $this->createTransaction(
                TransactionStatus::PENDING,
                $this->faker->randomNumber(3),
                'Vandar',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_11',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->setReferenceAndPersist(
            'transaction_22',
            $this->createTransaction(
                TransactionStatus::SUCCESS,
                $this->faker->randomNumber(3),
                'Vandar',
                $this->faker->randomNumber(3),
                $this->faker->randomNumber(3),
                'order_document_12',
                $this->faker->randomNumber(4),
                $this->faker->randomNumber()
            )
        );

        $this->manager->flush();
    }

    private function createTransaction(
        string $status,
        int $amount,
        string $gateway,
        string $referenceNumber,
        string $trackingNumber,
        string $orderDocument,
        string $identifier,
        ?string $token = null
    ): Transaction {
        return (new Transaction())->setStatus($status)
                                  ->setAmount($amount)
                                  ->setGateway($gateway)
                                  ->setReferenceNumber($referenceNumber)
                                  ->setTrackingNumber($trackingNumber)
                                  ->setDocument($this->getReference($orderDocument))
                                  ->setIdentifier($identifier)
                                  ->setToken($token);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            OrderDocumentFixtures::class
        ];
    }
}
