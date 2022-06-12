<?php

namespace App\Tests\Unit\Service\ORM\Extension\Utils;

use App\Service\ORM\Extension\Utils\QueryBuilderMethodInflector;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class QueryBuilderMethodInflectorTest
 */
final class QueryBuilderMethodInflectorTest extends MockeryTestCase
{
    public function testItThrowExceptionIfMethodIsInvalid()
    {
        $inflector = new QueryBuilderMethodInflector();

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid or unsupported operator "invalid_operator" given');

        $inflector->inflect('invalid_operator', 'value');
    }

    public function testItThrowExceptionIfValueIsNullButMethodIsNotIsNullOrIsNotNull()
    {
        $inflector = new QueryBuilderMethodInflector();

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid or unsupported operator ">" given');

        $inflector->inflect('>', null);
    }

    public function testItConvertEqualityOperatorToIsNullOrIsNotNullOperator()
    {
        $inflector = new QueryBuilderMethodInflector();

        self::assertEquals('isNull', $inflector->inflect('=', null));
        self::assertEquals('isNotNull', $inflector->inflect('!=', null));
    }

    /**
     * @dataProvider operatorProvider
     */
    public function testItInflectMethod(string $expectedMethod, string $operator, $value)
    {
        $inflector = new QueryBuilderMethodInflector();
        self::assertEquals($expectedMethod, $inflector->inflect($operator, 10));
    }

    public function operatorProvider()
    {
        return [
            ['between', 'BETWEEN', '1,10'],
            ['in', 'IN', '1,2'],
            ['like', 'LIKE', '%foo%'],
            ['gt', '>', 10],
            ['gte', '>=', 10],
            ['lt', '<', 10],
            ['lte', '<=', 10],
            ['neq', '!=', 'val'],
            ['eq', '=', 'val'],
            ['notIn', 'NOT_IN', '1,2'],
        ];
    }
}
