<?php

namespace App\Service\Zones;

use App\Entity\City;
use App\Entity\CityZone;
use App\Entity\District;
use App\Entity\DistrictZone;
use App\Entity\GenericZone;
use App\Entity\Province;
use App\Entity\ProvinceZone;
use App\Entity\Zone;
use App\DTO\ZoneFormData;

class ZoneFormDataFactory implements ZoneFormDataFactoryInterface
{
    public function create(string $type): ?ZoneFormData
    {
        $data = new ZoneFormData();

        switch ($type) {
            case 'province':
                $data->setZonesFieldClass(Province::class)
                    ->setZonesFieldName('provinces')
                    ->setZone(new ProvinceZone());
                break;
            case 'city':
                $data->setZonesFieldClass(City::class)
                    ->setZonesFieldName('cities')
                    ->setZone(new CityZone());
                break;
            case 'district':
                $data->setZonesFieldClass(District::class)
                    ->setZonesFieldName('districts')
                    ->setZone(new DistrictZone());
                break;
            case 'generic':
                $data->setZonesFieldClass(Zone::class)
                    ->setZonesFieldName('members')
                    ->setZone(new GenericZone());
                break;
            default:
                $data = null;
                break;
        }

        return $data;
    }
}
