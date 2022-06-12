<?php

namespace App\Repository;

use App\Dictionary\ShipmentTrackingCodeStatus;
use App\Entity\ShipmentTrackingCodeUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShipmentTrackingCodeUpdate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShipmentTrackingCodeUpdate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShipmentTrackingCodeUpdate[]    findAll()
 * @method ShipmentTrackingCodeUpdate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipmentTrackingCodeUpdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShipmentTrackingCodeUpdate::class);
    }

    public function getAdminUploadSheetErrors(string $adminUsername, int $fromMinutesAgo = 30): array
    {
        $uploadResultErrors = $this
            ->createQueryBuilder('shipmentTrackingCodeUpdate')
            ->select('shipmentTrackingCodeUpdate.errors')
            ->andWhere('shipmentTrackingCodeUpdate.createdBy = :admin')
            ->andWhere('shipmentTrackingCodeUpdate.status = :status')
            ->andWhere('shipmentTrackingCodeUpdate.createdAt >= :createdAt')
            ->orderBy("shipmentTrackingCodeUpdate.createdAt", "DESC")
            ->setParameters([
                'admin'   => $adminUsername,
                'status'  => ShipmentTrackingCodeStatus::PROCESSED,
                'createdAt' => date("Y-m-d H:i:s", strtotime("-$fromMinutesAgo minutes")),
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        $errors = [];
        if ($uploadErrors = $uploadResultErrors[0]['errors'] ?? []) {
            foreach ($uploadErrors as $error) {
                $errors[] = $error['errorMessage'];
            }
        }

        return $errors;
    }
}
