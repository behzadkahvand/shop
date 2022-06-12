<?php

namespace App\Service\ORM\Extension\Utils;

/**
 * Class OperatorAndValueExtractor
 */
class OperatorAndValueExtractor
{
    private const QUERY_STRING_TO_OPERATOR_MAP = [
        'btn'  => 'BETWEEN',
        'in'   => 'IN',
        'nin'  => 'NOT_IN',
        'like' => 'LIKE',
        'gt'   => '>',
        'gte'  => '>=',
        'lt'   => '<',
        'lte'  => '<=',
        'neq'  => '!=',
    ];

    public function extract($value): array
    {
        if (!is_array($value)) {
            return ['=' => $this->normalizeValue($value, '=')];
        }

        $results = [];
        foreach ($value as $operator => $val) {
            $key = self::QUERY_STRING_TO_OPERATOR_MAP[$operator] ?? '=';

            $results[$key] = $this->normalizeValue($val, $operator);
        }

        return $results;
    }

    /**
     * @param $value
     *
     * @return array|int
     */
    private function normalizeValue($value, string $operator)
    {
        $especialKeywords = ['null' => null, 'false' => false, 'true' => true];

        $toNumber = function ($v) {
            return 0 + $v;
        };

        $map = function ($v) use ($toNumber) {
            return is_numeric($v) ? $toNumber($v) : $v;
        };

        $filter = function ($value) {
            return '' !== $value;
        };

        switch (true) {
            case false !== strpos($value, ','):
                if (in_array($operator, ['btn', 'in', 'nin'])) {
                    return array_map(
                        $map,
                        array_values(array_filter(array_map('trim', explode(',', $value)), $filter))
                    );
                } else {
                    return trim($value);
                }
            case strpos(strtolower($value), 'e') === false && is_numeric($value):
                return $toNumber($value);
            case array_key_exists(strtolower($value), $especialKeywords):
                return $especialKeywords[strtolower($value)];
            default:
                return $value;
        }
    }
}
