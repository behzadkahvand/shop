<?php

namespace App\Service\ORM\Exceptions;

/**
 * Class CustomFilterNotFoundException
 */
final class CustomFilterNotFoundException extends \InvalidArgumentException
{
    /**
     * CustomFilterNotFoundException constructor.
     *
     * @param string $filter
     */
    public function __construct(string $filter)
    {
        parent::__construct(sprintf('Unable to locate %s custom filter!', $filter));
    }
}
