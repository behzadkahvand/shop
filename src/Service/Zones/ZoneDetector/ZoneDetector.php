<?php

namespace App\Service\Zones\ZoneDetector;

use App\Entity\CityZone;
use App\Entity\CustomerAddress;
use App\Entity\DistrictZone;
use App\Entity\OrderAddress;
use App\Entity\ProvinceZone;
use App\Entity\Zone;
use App\Service\Zones\ZoneDetector\Exceptions\ZoneNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ZoneDetector
 */
class ZoneDetector
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * ZoneDetector constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param CustomerAddress $address
     *
     * @return Zone
     */
    public function getZoneForCustomerAddress(CustomerAddress $address): Zone
    {
        $criteria = [
            DistrictZone::class => ['findOneByDistrict', $address->getDistrict()],
            CityZone::class     => ['findOneByCity', $address->getCity()],
            ProvinceZone::class => ['findOneByProvince', $address->getProvince()],
        ];

        return $this->getZone($criteria);
    }

    /**
     * @param OrderAddress $address
     *
     * @return Zone
     */
    public function getZoneForOrderAddress(OrderAddress $address): Zone
    {
        $criteria = [
            DistrictZone::class => ['findOneByDistrict', $address->getDistrict()],
            CityZone::class     => ['findOneByCity', $address->getCity()],
        ];

        return $this->getZone($criteria);
    }

    /**
     * @param array $criteria
     * @return mixed
     */
    protected function getZone(array $criteria)
    {
        foreach ($criteria as $entity => [$method, $value]) {
            if ($value && ($zone = $this->em->getRepository($entity)->{$method}($value))) {
                return $zone;
            }
        }

        throw new ZoneNotFoundException();
    }
}
