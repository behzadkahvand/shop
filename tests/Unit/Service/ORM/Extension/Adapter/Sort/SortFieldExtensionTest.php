<?php

namespace App\Tests\Unit\Service\ORM\Extension\Adapter\Sort;

use App\Service\ORM\Extension\Adapter\Sort\SortFieldExtension;
use App\Service\ORM\QueryContext;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SortFieldExtensionTest
 */
final class SortFieldExtensionTest extends MockeryTestCase
{
    /**
     * @var ManagerRegistry|\Mockery\MockInterface
     */
    private $registery;

    /**
     * @var SortFieldExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->registery = \Mockery::mock(ManagerRegistry::class);
        $this->extension = new SortFieldExtension($this->registery);
    }

    protected function tearDown(): void
    {
        $this->registery = null;

        unset($this->extension);
    }

    public function testItSkipIfThereIsNoFilter()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnFalse();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipIfFieldIsNested()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getSorts')->once()->withNoArgs()->andReturn(['foo.bar' => 10]);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipIfClassDoesNotHaveGivenField()
    {
        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->shouldReceive('hasField')->with(\Mockery::type('string'))->andReturnFalse();

        $om = \Mockery::mock(ObjectManager::class);
        $om->shouldReceive('getClassMetadata')->once()->andReturn($classMetadata);

        $this->registery->shouldReceive('getManagerForClass')->once()->andReturn($om);

        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getSorts')->once()->withNoArgs()->andReturn(['foo']);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSortByField()
    {
        $value = 'foo';

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->shouldReceive('hasField')->with(\Mockery::type('string'))->andReturnTrue();

        $om = \Mockery::mock(ObjectManager::class);
        $om->shouldReceive('getClassMetadata')->once()->andReturn($classMetadata);

        $this->registery->shouldReceive('getManagerForClass')->once()->andReturn($om);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('addOrderBy')->with("stdClass.{$value}", 'ASC')->andReturnSelf();

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasSort')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getCurrentAlias')->once()->withNoArgs()->andReturn('stdClass');
        $context->shouldReceive('getSorts')->once()->withNoArgs()->andReturn([$value]);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }
}
