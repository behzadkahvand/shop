<?php

namespace App\Repository;

use App\Dictionary\TransactionStatus;
use App\Entity\RefundDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RefundDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefundDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefundDocument[]    findAll()
 * @method RefundDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefundDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefundDocument::class);
    }

    public function getOrderRefundDocumentsData(int $orderId): array
    {
        $result = $this->createQueryBuilder('RefundDocument')
                    ->select('COALESCE(sum(RefundDocument.amount), 0) as totalOrderRefundDocumentAmounts')
                    ->leftJoin(
                        'RefundDocument.transactions',
                        'Transactions',
                        Join::WITH,
                        'Transactions.status = :success'
                    )
                    ->addSelect('COALESCE(sum(Transactions.amount), 0) as totalRefundTransactionAmounts')
                    ->where('IDENTITY(RefundDocument.order) = :orderId')
                    ->andWhere('Transactions.status = :success')
                    ->setParameters([
                        'orderId' => $orderId,
                        'success' => TransactionStatus::SUCCESS
                    ])
                    ->getQuery()
                    ->getSingleResult();

        return array_map(fn($value) => (int)$value, $result);
    }
}
