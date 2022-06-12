<?php

namespace App\Repository;

use App\Dictionary\HolidayTypeDictionary;
use App\Entity\Holiday;
use App\Entity\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Holiday|null find($id, $lockMode = null, $lockVersion = null)
 * @method Holiday|null findOneBy(array $criteria, array $orderBy = null)
 * @method Holiday[]    findAll()
 * @method Holiday[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HolidayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Holiday::class);
    }

    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('holiday');
    }

    /**
     * @param int $type
     * @param \DateTimeInterface $dateTime
     * @param Seller ...$sellers
     *
     * @return bool
     */
    public function hasHolidayOfType(int $type, \DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        if (!HolidayTypeDictionary::isValid($type)) {
            throw new \InvalidArgumentException('Invalid holiday type.');
        }

        $query = $this->createQueryBuilder('h')
                      ->select('COUNT(h)')
                      ->where('h.date = :date AND h.supply = :type')
                      ->setParameter('date', $dateTime->format('Y-m-d'))
                      ->setParameter('type', (bool) $type);

        if (0 < count($sellers)) {
            $query->andWhere('h.seller IN (:sellers)')->setParameter('sellers', $sellers);
        } else {
            $query->andWhere('h.seller IS NULL');
        }

        $count = (int) $query->getQuery()->getSingleScalarResult();

        return 0 === $count;
    }
}
