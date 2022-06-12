<?php

namespace App\Service\Utils;

use LongitudeOne\Spatial\PHP\Types\AbstractPoint;

class PointService
{
    public function convertToDatabaseValue(AbstractPoint $value): string
    {
        return sprintf('POINT(%s %s)', $value->getLongitude(), $value->getLatitude());
    }
}
