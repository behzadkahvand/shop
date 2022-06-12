<?php

namespace App\Repository;

use App\Entity\CategoryProductIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryProductIdentifier|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryProductIdentifier|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryProductIdentifier[]    findAll()
 * @method CategoryProductIdentifier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryProductIdentifierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryProductIdentifier::class);
    }
}
