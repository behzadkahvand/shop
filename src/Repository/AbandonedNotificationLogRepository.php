<?php

namespace App\Repository;

use App\Entity\AbandonedNotificationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbandonedNotificationLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbandonedNotificationLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbandonedNotificationLog[]    findAll()
 * @method AbandonedNotificationLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbandonedNotificationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbandonedNotificationLog::class);
    }

    public function findOneByCartId($cartId): ?AbandonedNotificationLog
    {
        return $this->createQueryBuilder('log')
            ->select('PARTIAL log.{id, total_sent}')
            ->andWhere('log.cart = :cart_id')
            ->setParameter('cart_id', $cartId)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
