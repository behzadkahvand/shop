<?php

namespace App\Faker\Provider;

use DateTime;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

final class DateTimeProvider extends BaseProvider
{
    /**
     * DateTimeProvider constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    /**
     * Generate the specific time
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return DateTime
     */
    public function specificTime(int $hour, int $minute = 0, int $second = 0): DateTime
    {
        return (new DateTime())->setTime($hour, $minute, $second);
    }
}
