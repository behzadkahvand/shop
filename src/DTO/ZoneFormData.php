<?php

namespace App\DTO;

use App\Entity\Zone;

class ZoneFormData
{
    private string $zonesFieldName;

    private string $zonesFieldClass;

    private Zone $zone;

    public function getZonesFieldName(): string
    {
        return $this->zonesFieldName;
    }

    public function setZonesFieldName(string $zonesFieldName): ZoneFormData
    {
        $this->zonesFieldName = $zonesFieldName;
        return $this;
    }

    public function getZonesFieldClass(): string
    {
        return $this->zonesFieldClass;
    }

    public function setZonesFieldClass(string $zonesFieldClass): ZoneFormData
    {
        $this->zonesFieldClass = $zonesFieldClass;
        return $this;
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): ZoneFormData
    {
        $this->zone = $zone;
        return $this;
    }
}
