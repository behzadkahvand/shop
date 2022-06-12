<?php

namespace App\Service\Zones;

use App\DTO\ZoneFormData;

interface ZoneFormDataFactoryInterface
{
    public function create(string $type): ?ZoneFormData;
}
