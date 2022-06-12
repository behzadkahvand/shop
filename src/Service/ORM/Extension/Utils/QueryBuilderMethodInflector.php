<?php

namespace App\Service\ORM\Extension\Utils;

/**
 * Class MethodInflector
 */
class QueryBuilderMethodInflector
{
    private const OPERATORS_TO_METHOD_MAP = [
        'BETWEEN' => 'between',
        'IN'      => 'in',
        'LIKE'    => 'like',
        '>'       => 'gt',
        '>='      => 'gte',
        '<'       => 'lt',
        '<='      => 'lte',
        '!='      => 'neq',
        '='       => 'eq',
        'NOT_IN'  => 'notIn',
    ];

    public function inflect($operator, $value): string
    {
        $method = self::OPERATORS_TO_METHOD_MAP[$operator] ?? null;

        if (null === $value) {
            if (in_array($method, ['eq', 'neq'])) {
                $method = $method === 'eq' ? 'isNull' : 'isNotNull';
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Invalid or unsupported operator "%s" given', $operator),
                    400
                );
            }
        }

        if (null === $method) {
            throw new \InvalidArgumentException(
                sprintf('Invalid or unsupported operator "%s" given', $operator),
                400
            );
        }

        return $method;
    }
}
