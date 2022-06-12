<?php

namespace App\Tests\Unit\Service\ORM\Extension\Utils;

use App\Service\ORM\Extension\Utils\OperatorAndValueExtractor;
use PHPUnit\Framework\TestCase;

/**
 * Class OperatorAndValueExtractorTest
 */
final class OperatorAndValueExtractorTest extends TestCase
{
    /**
     * @dataProvider normalizeValueProvider
     */
    public function testItNormalizeValues(string $value, $expectedValue)
    {
        $extractor = new OperatorAndValueExtractor();

        self::assertEquals($expectedValue, $extractor->extract($value));
    }

    /**
     * @dataProvider operatorAndValueProvider
     */
    public function testItExtractOperatorAndValues(array $value, $expectedValue)
    {
        $extractor = new OperatorAndValueExtractor();

        self::assertEquals($expectedValue, $extractor->extract($value));
    }

    public function normalizeValueProvider()
    {
        return [
            ['12', ['=' => 12]],
            ['1,2', ['=' => '1,2']],
            ['1', ['=' => 1]],
            ['null', ['=' => null]],
            ['false', ['=' => false]],
            ['true', ['=' => true]],
            ['foo', ['=' => 'foo']],
        ];
    }

    public function operatorAndValueProvider()
    {
        return [
            [['btn' => '1,2'], ['BETWEEN' => [1, 2]]],
            [['in' => '1,2'], ['IN' => [1, 2]]],
            [['nin' => '1,2'], ['NOT_IN' => [1, 2]]],
            [['like' => '%foo%'], ['LIKE' => '%foo%']],
            [['gt' => '10'], ['>' => 10]],
            [['gte' => '10'], ['>=' => 10]],
            [['lt' => '10'], ['<' => 10]],
            [['lte' => '10'], ['<=' => 10]],
            [['neq' => '10'], ['!=' => 10]],
            [['invalid_operator' => '10'], ['=' => 10]],
        ];
    }
}
