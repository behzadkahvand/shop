<?php

namespace App\Dictionary;

use ReflectionClass;
use ReflectionException;

abstract class Dictionary
{
    /**
     * Get all registered constants
     *
     * @return array
     * @throws ReflectionException
     */
    final public static function toArray()
    {
        return (new ReflectionClass(get_called_class()))->getConstants();
    }

    /**
     * @param $value
     *
     * @return bool
     * @throws ReflectionException
     */
    final public static function isValid($value): bool
    {
        return in_array($value, self::toArray(), true);
    }
}
