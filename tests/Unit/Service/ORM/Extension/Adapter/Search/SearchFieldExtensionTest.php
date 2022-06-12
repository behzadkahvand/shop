<?php

namespace App\Tests\Unit\Service\ORM\Extension\Adapter\Search;

use App\Service\ORM\Extension\Adapter\Search\SearchFieldExtension;
use App\Service\ORM\Extension\Utils\OperatorAndValueExtractor;
use App\Service\ORM\Extension\Utils\QueryBuilderMethodInflector;
use App\Service\ORM\Extension\Utils\QueryBuilderMethodInvoker;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SearchFieldExtensionTest
 */
final class SearchFieldExtensionTest extends MockeryTestCase
{
    /**
     * @var ManagerRegistry|\Mockery\MockInterface
     */
    private $registery;

    /**
     * @var OperatorAndValueExtractor|\Mockery\MockInterface
     */
    private $operatorAndValueExtractor;

    /**
     * @var QueryBuilderMethodInflector|\Mockery\MockInterface
     */
    private $methodInflector;

    /**
     * @var QueryBuilderMethodInvoker|\Mockery\MockInterface
     */
    private $methodInvoker;

    /**
     * @var SearchFieldExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->registery                 = \Mockery::mock(ManagerRegistry::class);
        $this->operatorAndValueExtractor = \Mockery::mock(OperatorAndValueExtractor::class);
        $this->methodInflector           = \Mockery::mock(QueryBuilderMethodInflector::class);
        $this->methodInvoker             = \Mockery::mock(QueryBuilderMethodInvoker::class);
        $this->extension                 = new SearchFieldExtension(
            $this->registery,
            $this->operatorAndValueExtractor,
            $this->methodInflector,
            $this->methodInvoker
        );
    }

    protected function tearDown(): void
    {
        $this->registery = null;
        $this->operatorAndValueExtractor = null;
        $this->methodInflector = null;
        $this->methodInvoker = null;

        unset($this->extension);
    }

    public function testItSkipIfThereIsNoFilter()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnFalse();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItSkipIfFieldIsNested()
    {
        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getFilters')->once()->withNoArgs()->andReturn(['foo.bar' => 10]);

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
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getFilters')->once()->withNoArgs()->andReturn(['foo' => 10]);

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }

    public function testItFilterByField()
    {
        $operator = '=';
        $value    = 10;

        $classMetadata = \Mockery::mock(ClassMetadata::class);
        $classMetadata->shouldReceive('hasField')->with(\Mockery::type('string'))->andReturnTrue();

        $om = \Mockery::mock(ObjectManager::class);
        $om->shouldReceive('getClassMetadata')->once()->andReturn($classMetadata);

        $this->registery->shouldReceive('getManagerForClass')->once()->andReturn($om);

        $qb = \Mockery::mock(QueryBuilder::class);

        $context = \Mockery::mock(QueryContext::class);
        $context->shouldReceive('hasFilters')->once()->withNoArgs()->andReturnTrue();
        $context->shouldReceive('getCurrentAlias')->once()->withNoArgs()->andReturn('stdClass');
        $context->shouldReceive('getFilters')->once()->withNoArgs()->andReturn(['foo' => $value]);

        $this->operatorAndValueExtractor->shouldReceive('extract')
                                        ->once()
                                        ->with($value)
                                        ->andReturn([$operator => $value]);

        $this->methodInflector->shouldReceive('inflect')->once()->with($operator, $value)->andReturn('eq');

        $this->methodInvoker->shouldReceive('invoke')
                            ->once()
                            ->with($qb, 'eq', 'stdClass.foo', $operator, $value)
                            ->andReturn();

        $this->extension->applyToCollection($qb, \stdClass::class, $context);
    }
}
