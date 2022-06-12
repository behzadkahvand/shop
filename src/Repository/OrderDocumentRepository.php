<?php

namespace App\Repository;

use App\Dictionary\TransactionStatus;
use App\Entity\OrderDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDocument[]    findAll()
 * @method OrderDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDocument::class);
    }

    public function getOrderDocumentData(int $orderId): array
    {
        $result = $this->createQueryBuilder('OrderDocument')
                    ->select('COALESCE(OrderDocument.amount, 0) as orderDocumentAmount')
                    ->innerJoin('OrderDocument.order', 'Orders')
                    ->leftJoin(
                        'OrderDocument.transactions',
                        'Transactions',
                        Join::WITH,
                        'Transactions.status = :success'
                    )
                    ->addSelect('COALESCE(sum(Transactions.amount), 0) as totalTransactionAmounts')
                    ->where('Orders.id = :orderId')
                    ->setParameters([
                        'orderId' => $orderId,
                        'success' => TransactionStatus::SUCCESS
                    ])
                    ->getQuery()
                    ->getSingleResult();

        return array_map(fn($value) => (int)$value, $result);
    }
}
