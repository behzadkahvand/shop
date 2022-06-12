<?php

namespace App\Tests\Unit\Service\ORM\Extension\Join;

use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerData;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryJoinerTest
 */
final class QueryJoinerTest extends TestCase
{
    public function testItThrowExceptionIfJoinTypeIsInvalid()
    {
        $invalidJoinType = 3;
        $joiner          = new QueryJoiner();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid join type given.');

        $joiner->join(
            \Mockery::mock(QueryBuilder::class),
            \Mockery::mock(QueryContext::class),
            new QueryJoinerData('foo', 'bar', 'Foo', 'Bar'),
            $invalidJoinType
        );
    }

    public function testItApplyInnerJoin()
    {
        $entityClass   = 'Foo';
        $relationClass = 'Bar';
        $entityAlias   = 'foo';
        $relationField = 'bar';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('innerJoin')->once()->with('foo.bar', \Mockery::type('string'))->andReturnSelf();
        $queryBuilder->shouldReceive('addSelect')->once()->with(\Mockery::type('string'))->andReturnSelf();

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('setAlias')
                ->once()
                ->with($entityClass, $relationClass, \Mockery::type('string'))
                ->andReturnSelf();
        $context->shouldReceive('changeCurrentAlias')->once()->with(\Mockery::type('string'))->andReturnSelf();

        $joiner = new QueryJoiner();
        $result = $joiner->join(
            $queryBuilder,
            $context,
            new QueryJoinerData($entityAlias, $relationField, $entityClass, $relationClass),
            QueryJoiner::JOIN_TYPE_INNER
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals($relationClass, $result[0]);
        self::assertTrue(is_string($result[1]));
    }

    public function testItApplyLeftJoin()
    {
        $entityClass   = 'Foo';
        $relationClass = 'Bar';
        $entityAlias   = 'foo';
        $relationField = 'bar';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('leftJoin')->once()->with('foo.bar', \Mockery::type('string'))->andReturnSelf();
        $queryBuilder->shouldNotReceive('addSelect');

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('setAlias')
                ->once()
                ->with($entityClass, $relationClass, \Mockery::type('string'))
                ->andReturnSelf();
        $context->shouldReceive('changeCurrentAlias')->once()->with(\Mockery::type('string'))->andReturnSelf();

        $joiner = new QueryJoiner();
        $result = $joiner->join(
            $queryBuilder,
            $context,
            new QueryJoinerData($entityAlias, $relationField, $entityClass, $relationClass),
            QueryJoiner::JOIN_TYPE_LEFT
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals($relationClass, $result[0]);
        self::assertTrue(is_string($result[1]));
    }
}
