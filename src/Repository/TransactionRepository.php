<?php

namespace App\Repository;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransactionStatus;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\Transaction;
use App\Service\Payment\Gateways\HamrahCardGateway;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getGatewayByIdentifier(string $identifier): ?string
    {
        $result = $this->createQueryBuilder('transaction')
            ->where('transaction.identifier = :identifier')
            ->select('transaction.gateway')
            ->setParameters(compact('identifier'))
            ->getQuery()
            ->getScalarResult();

        return $result[0]['gateway'] ?? null;
    }

    public function findAllPendingTransactionsAfterSpecificTime(DateTimeImmutable $time)
    {
        return $this
            ->createQueryBuilder('Transactions')
            ->innerJoin('Transactions.document', 'Document')
            ->innerJoin(OrderDocument::class, 'OrderDocument', Join::WITH, 'Document.id = OrderDocument.id')
            ->innerJoin('OrderDocument.order', '_order')
            ->andWhere('_order.paymentMethod != :paymentMethod')
            ->andWhere('Transactions.status = :status')
            ->andWhere('Transactions.createdAt < :created')
            ->setParameters([
                'status' => TransactionStatus::PENDING,
                'created' => $time,
                'paymentMethod' => OrderPaymentMethod::OFFLINE,
            ])
            ->getQuery()
            ->getResult();
    }

    public function getHamrahCardTransaction(int $orderId): ?Transaction
    {
        $result = $this->createQueryBuilder('Transactions')
            ->leftJoin('Transactions.document', 'Document')
            ->leftJoin(
                Order::class,
                'orders',
                Join::WITH,
                'orders.orderDocument = Document.id'
            )
            ->where('orders.id = :orderId')
            ->andWhere('orders.status = :orderStatus')
            ->andWhere('Transactions.gateway = :gateway')
            ->andWhere('Transactions.status = :transactionStatus')
            ->andWhere('Transactions.createdAt > :created')
            ->setParameters([
                'orderId' => $orderId,
                'orderStatus' => OrderStatus::WAITING_FOR_PAY,
                'gateway' => HamrahCardGateway::getName(),
                'transactionStatus' => TransactionStatus::PENDING,
                'created' => (new DateTimeImmutable('-1 hour'))->format('Y-m-d H:i:s'),
            ])
            ->getQuery()
            ->getResult();

        return ! empty($result) ? $result[0] : null;
    }
}
