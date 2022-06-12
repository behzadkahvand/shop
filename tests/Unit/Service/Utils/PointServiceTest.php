<?php

namespace App\Tests\Unit\Service\Utils;

use App\Service\Utils\PointService;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PointServiceTest extends MockeryTestCase
{
    public function testItCanConvertPointToDatabaseValue(): void
    {
        $result = (new PointService())->convertToDatabaseValue(new Point(51.43140036011, 35.723699543507));

        self::assertEquals('POINT(51.43140036011 35.723699543507)', $result);
    }
}
