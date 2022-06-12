<?php

namespace App\Tests\Unit\Service\ORM\Extension\Adapter\Search;

use App\Service\ORM\Extension\Adapter\Search\SearchFieldExtension;
use App\Service\ORM\Extension\Adapter\Search\SearchNestedFieldExtension;
use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerData;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SearchNestedFieldExtensionTest
 */
final class SearchNestedFieldExtensionTest extends MockeryTestCase
{
    /**
     * @var ManagerRegistry|\Mockery\MockInterface
     */
    private $registery;

    /**
     * @var SearchFieldExtension
     */
    private $fieldExtension;

    /**
     * @var QueryJoiner|\Mockery\MockInterface
     */
    private $queryJoiner;

    /**
     * @var SearchNestedFieldExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->registery      = \Mockery::mock(ManagerRegistry::class);
        $this->fieldExtension = \Mockery::mock(SearchFieldExtension::class);
        $this->queryJoiner    = \Mockery::mock(QueryJoiner::class);
        $this->extension      = new SearchNestedFieldExtension(
            $this->fieldExtension,
            $this->registery,
            $this->queryJoiner
        );
    }

    protected function tearDown(): void
    {
        $this->registery = null;
        $this->fieldExtension = null;
        $this->queryJoiner = null;

        unset($this->extension);
    }

    public function testItSkipIfThereIsNoFilter()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnFalse();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipIfFieldIsNotNested()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getFilters')->once()->withNoArgs()->andReturn(['foo' => 10]);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipItFilterByNestedField()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->shouldReceive('hasAssociation')->once()->with('foo')->andReturnTrue();
        $classMetadata->shouldReceive('getAssociationTargetClass')->once()->andReturn('Foo');

        $om = \Mockery::mock(ClassMetadata::class);
        $om->shouldReceive('getClassMetadata')->once()->with('stdClass')->andReturn($classMetadata);

        $this->registery->shouldReceive('getManagerForClass')->once()->with('stdClass')->andReturn($om);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->with()->andReturnTrue();
        $context->shouldReceive('getFilters')->once()->withNoArgs()->andReturn(['foo.bar' => 10]);
        $context->shouldReceive('getRootAlias')->once()->withNoArgs()->andReturn('stdClass');
        $context->shouldReceive('hasAlias')->once()->with('stdClass', 'Foo')->andReturnFalse();
        $context->shouldNotReceive('getAlias');
        $context->shouldNotReceive('changeCurrentAlias');
        $context->shouldReceive('withFilters')->once()->with(['bar' => 10])->andReturnSelf();
        $context->shouldReceive('unsetCurrentAlias')->once()->withNoArgs()->andReturnSelf();

        $this->queryJoiner->shouldReceive('join')
                          ->once()
                          ->with($qb, $context, \Mockery::type(QueryJoinerData::class), QueryJoiner::JOIN_TYPE_INNER)
                          ->andReturn(['Foo', 'stdClass_foo']);

        $this->fieldExtension->shouldReceive('applyToCollection')
                             ->once()
                             ->with($qb, 'Foo', \Mockery::type(QueryContext::class))
                             ->once();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }
}
