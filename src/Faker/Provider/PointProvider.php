<?php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

/**
 * Class PointProvider
 */
final class PointProvider extends BaseProvider
{
    /**
     * PointProvider constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    /**
     * Generate a point
     *
     * @return Point
     */
    public function point(): Point
    {
        return new Point([$this->generator->longitude(), $this->generator->latitude()]);
    }
}
