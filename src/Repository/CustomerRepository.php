<?php

namespace App\Repository;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param UserInterface $user
     * @param string $newEncodedPassword
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getCustomerQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('customer');
    }

    public function getIdsByMobileList(array $mobiles)
    {
        $qb = $this->createQueryBuilder('customer');
        $qb
            ->select('customer.id')
            ->andWhere($qb->expr()->in('customer.mobile', ':mobiles'))
            ->setParameter(':mobiles', $mobiles);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    public function findCustomersWithDeliveredShipmentSince2DayAgo(): iterable
    {
        $qb = $this->createQueryBuilder('customer')
                   ->select('DISTINCT customer.id')
                   ->innerJoin('customer.orders', 'orders')
                   ->innerJoin('orders.shipments', 'shipments')
                   ->innerJoin('shipments.orderShipmentStatusLogs', 'logs')
                   ->where('logs.statusTo = :delivered AND DATE(logs.createdAt) = :two_day_ago')
                   ->setParameters([
                                       'delivered'   => OrderShipmentStatus::DELIVERED,
                                       'two_day_ago' => date('Y-m-d', strtotime('-2 day')),
                                   ]);

        $ids = array_column($qb->getQuery()->getScalarResult(), 'id');

        foreach (array_chunk($ids, 100) as $idsGroup) {
            $qb = $this->createQueryBuilder('customer')
                       ->select(['customer.name', 'customer.mobile'])
                       ->where('customer.id IN (:idsGroup)')
                       ->setParameters(compact('idsGroup'));

            foreach ($qb->getQuery()->getScalarResult() as $row) {
                yield (new Customer())->setName($row['name'])
                                      ->setMobile($row['mobile']);
            }
        }
    }

    public function getCustomersByIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('customer');
        $qb
            ->where($qb->expr()->in('customer.id', ':ids'))
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
