<?php

namespace App\Tests\Unit\Service\ORM\Extension\Adapter\Sort;

use App\Service\ORM\Extension\Adapter\Search\SearchNestedFieldExtension;
use App\Service\ORM\Extension\Adapter\Sort\SortFieldExtension;
use App\Service\ORM\Extension\Adapter\Sort\SortNestedFieldExtension;
use App\Service\ORM\Extension\Join\QueryJoiner;
use App\Service\ORM\Extension\Join\QueryJoinerData;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SortNestedFieldExtensionTest
 */
final class SortNestedFieldExtensionTest extends MockeryTestCase
{
    /**
     * @var ManagerRegistry|\Mockery\MockInterface
     */
    private $registery;

    /**
     * @var SortFieldExtension|\Mockery\MockInterface
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
        $this->fieldExtension = \Mockery::mock(SortFieldExtension::class);
        $this->queryJoiner    = \Mockery::mock(QueryJoiner::class);
        $this->extension      = new SortNestedFieldExtension(
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

    public function testItSkipIfThereIsNoSort()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnFalse();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipIfFieldIsNotNested()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getSorts')->once()->withNoArgs()->andReturn(['foo']);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipItSortByNestedField()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->shouldReceive('hasAssociation')->once()->with('foo')->andReturnTrue();
        $classMetadata->shouldReceive('getAssociationTargetClass')->once()->andReturn('Foo');

        $om = \Mockery::mock(ClassMetadata::class);
        $om->shouldReceive('getClassMetadata')->once()->with('stdClass')->andReturn($classMetadata);

        $this->registery->shouldReceive('getManagerForClass')->once()->with('stdClass')->andReturn($om);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->with()->andReturnTrue();
        $context->shouldReceive('getSorts')->once()->withNoArgs()->andReturn(['foo.bar']);
        $context->shouldReceive('getRootAlias')->once()->withNoArgs()->andReturn('stdClass');
        $context->shouldReceive('hasAlias')->once()->with('stdClass', 'Foo')->andReturnFalse();
        $context->shouldNotReceive('getAlias');
        $context->shouldNotReceive('changeCurrentAlias');
        $context->shouldReceive('withSorts')->once()->with(['bar'])->andReturnSelf();
        $context->shouldReceive('unsetCurrentAlias')->once()->withNoArgs()->andReturnSelf();

        $this->queryJoiner->shouldReceive('join')
                          ->once()
                          ->with($qb, $context, \Mockery::type(QueryJoinerData::class), QueryJoiner::JOIN_TYPE_LEFT)
                          ->andReturn(['Foo', 'stdClass_foo']);

        $this->fieldExtension->shouldReceive('applyToCollection')
                             ->once()
                             ->with($qb, 'Foo', \Mockery::type(QueryContext::class))
                             ->once();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }
}
