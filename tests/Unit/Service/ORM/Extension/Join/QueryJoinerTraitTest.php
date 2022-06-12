<?php

namespace App\Tests\Unit\Service\ORM\Extension\Join;

use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerData;
use App\Service\ORM\Extension\Join\QueryJoinerTrait;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class QueryJoinerTraitTest
 */
final class QueryJoinerTraitTest extends MockeryTestCase
{
    use QueryJoinerTrait;

    private string $relationClass;

    public function testItExtractFieldFromRelations()
    {
        self::assertEquals([['foo', 'bar'], 'baz'], $this->extractFieldFromRelations('foo.bar.baz'));
    }

    public function testItUseAliasOfAlreadyJoinedRelation()
    {
        $resourceClass = 'Foo\\Bar\\BazClass';
        $relationClass = 'Foo\\Bar\\BamClass';

        $this->relationClass = $relationClass;

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('getRootAlias')->once()->withNoArgs()->andReturn('root_alias');
        $context->shouldReceive('hasAlias')->once()->with($resourceClass, $relationClass)->andReturnTrue();
        $context->shouldReceive('getAlias')->once()->with($resourceClass, $relationClass)->andReturn('existing_alias');
        $context->shouldReceive('changeCurrentAlias')->once()->with('existing_alias')->andReturn();

        $result = $this->applyJoins(
            $resourceClass,
            [$relationClass],
            $context,
            \Mockery::mock(QueryBuilder::class),
            QueryJoiner::JOIN_TYPE_INNER
        );

        self::assertEquals($relationClass, $result);
    }

    public function testItJoinResourceToRelation()
    {
        $resourceClass = 'Foo\\Bar\\BazClass';
        $relationClass = 'Foo\\Bar\\BamClass';

        $queryBuilder        = \Mockery::mock(QueryBuilder::class);
        $this->relationClass = $relationClass;

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('getRootAlias')->once()->withNoArgs()->andReturn('root_alias');
        $context->shouldReceive('hasAlias')->once()->with($resourceClass, $relationClass)->andReturnFalse();

        $joinData = \Mockery::type(QueryJoinerData::class);

        $this->queryJoiner = \Mockery::mock(QueryJoiner::class);
        $this->queryJoiner->shouldReceive('join')
                          ->once()
                          ->with($queryBuilder, $context, $joinData, QueryJoiner::JOIN_TYPE_INNER)
                          ->andReturn([$relationClass, 'joined_alias']);

        $result = $this->applyJoins(
            $resourceClass,
            [$relationClass],
            $context,
            $queryBuilder,
            QueryJoiner::JOIN_TYPE_INNER
        );

        self::assertEquals($relationClass, $result);
    }

    private function getRelationClass(string $resourceClass, string $relation): string
    {
        return $this->relationClass;
    }
}
