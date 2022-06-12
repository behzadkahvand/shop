<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Serializer\Normalizer\PointNormalizer;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\TestCase;
use stdClass;

class PointNormalizerTest extends TestCase
{
    protected PointNormalizer $pointNormalizer;

    protected Point $point;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pointNormalizer = new PointNormalizer();

        $this->point = new Point(51.25115, 35.51455);
    }

    protected function tearDown(): void
    {
        unset($this->pointNormalizer, $this->point);
    }

    public function testItSupportsInstancesOfAbstractPoint()
    {
        self::assertTrue($this->pointNormalizer->supportsNormalization($this->point));
    }

    public function testItDoesntSupportInstancesOfAbstractPoint()
    {
        self::assertFalse($this->pointNormalizer->supportsNormalization(new stdClass()));
    }

    public function testItNormalizePoint()
    {
        $result = $this->pointNormalizer->normalize($this->point);

        self::assertCount(2, $result);
        self::assertArrayHasKey('lat', $result);
        self::assertArrayHasKey('long', $result);
        self::assertEquals(35.51455, $result['lat']);
        self::assertEquals(51.25115, $result['long']);
    }
}
