<?php

namespace App\Service\ORM\Extension;

/**
 * Class QueryParameterNameGenerator.
 */
final class QueryAliasNameGenerator
{
    /**
     * @var int
     */
    private static int $parameterNumber = 1;

    /**
     * @var array
     */
    private static array $aliases = [];

    /**
     * @param string $association
     *
     * @return string
     */
    public static function generate(string $association): ?string
    {
        if (isset(self::$aliases[$association])) {
            return self::$aliases[$association] . '_' . self::$parameterNumber++;
        }

        return self::$aliases[$association] = str_replace('.', '_', $association) . '_' . self::$parameterNumber++;
    }
}
