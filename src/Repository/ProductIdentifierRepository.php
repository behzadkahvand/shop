<?php

namespace App\Repository;

use App\Entity\ProductIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductIdentifier|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductIdentifier|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductIdentifier[]    findAll()
 * @method ProductIdentifier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductIdentifierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductIdentifier::class);
    }
}
