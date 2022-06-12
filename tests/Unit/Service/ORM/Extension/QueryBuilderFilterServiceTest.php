<?php

namespace App\Tests\Unit\Service\ORM\Extension;

use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\Extension\QueryBuilderExtensionInterface;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class QueryBuilderFilterServiceTest
 */
final class QueryBuilderFilterServiceTest extends MockeryTestCase
{
    public function testItUseGivenQueryBuilder()
    {
        $qb            = \Mockery::mock(QueryBuilder::class);
        $resourceClass = 'Foo\\Bar\\Resource';
        $context       = [];

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldNotReceive('getManagerForClass');

        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $filterService = new QueryBuilderFilterService($registry, $dispatcher, []);
        self::assertSame($qb, $filterService->filter($resourceClass, $context, $qb));
    }

    public function testItApplyExtensionsToQueryBuilder()
    {
        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('getRootAliases')
           ->once()
           ->withNoArgs()
           ->andReturn(['resource_alias']);

        $resourceClass = 'Foo\\Bar\\Resource';
        $context       = [
            'filter' => ['foo.bar' => 10],
        ];

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldNotReceive('getManagerForClass');

        $extension = \Mockery::mock(QueryBuilderExtensionInterface::class);
        $extension->shouldReceive('applyToCollection')
                  ->once()
                  ->with($qb, $resourceClass, \Mockery::type(QueryContext::class))
                  ->andReturn();

        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterApplyingEvent::class))
                   ->andReturn();
        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterAppliedEvent::class))
                   ->andReturn();

        $filterService = new QueryBuilderFilterService($registry, $dispatcher, [$extension]);

        self::assertSame($qb, $filterService->filter($resourceClass, $context, $qb));
    }

    public function testItCreateQueryBuilderAndApplyExtensionsToIt()
    {
        $resourceClass = 'Foo\\Bar\\Resource';
        $context       = [
            'filter' => ['foo.bar' => 10],
        ];

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('getRootAliases')
           ->once()
           ->withNoArgs()
           ->andReturn(['resource_alias']);

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
                 ->once()
                 ->with($resourceClass)
                 ->andReturnUsing(function () use ($resourceClass, $qb) {
                     $em = \Mockery::mock(EntityManagerInterface::class);
                     $em->shouldReceive('getRepository')
                        ->once()
                        ->with($resourceClass)
                        ->andReturnUsing(function () use ($qb) {
                            $repo = \Mockery::mock(ObjectRepository::class);
                            $repo->shouldReceive('createQueryBuilder')
                                 ->once()
                                 ->with(\Mockery::type('string'))
                                 ->andReturn($qb);

                            return $repo;
                        });

                     return $em;
                 });

        $extension = \Mockery::mock(QueryBuilderExtensionInterface::class);
        $extension->shouldReceive('applyToCollection')
                  ->once()
                  ->with($qb, $resourceClass, \Mockery::type(QueryContext::class))
                  ->andReturn();

        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterApplyingEvent::class))
                   ->andReturn();
        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterAppliedEvent::class))
                   ->andReturn();

        $filterService = new QueryBuilderFilterService($registry, $dispatcher, [$extension]);
        self::assertSame($qb, $filterService->filter($resourceClass, $context));
    }

    public function testGettingAndSettingJoinMap()
    {
        $joinMap = QueryBuilderFilterService::getJoinMap();
        self::assertEmpty($joinMap);

        $newJoinMap = ['foo' => ['bar' => 'foobar']];

        QueryBuilderFilterService::setJoinMap($newJoinMap);

        self::assertSame($newJoinMap, QueryBuilderFilterService::getJoinMap());
    }

    public function testAddingToJoinMap()
    {
        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('getRootAliases')
           ->once()
           ->withNoArgs()
           ->andReturn(['resource_alias']);

        $resourceClass = 'Foo\\Bar\\Resource';
        $context       = [
            'filter' => ['foo.bar' => 10],
        ];

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldNotReceive('getManagerForClass');

        $extension = new class ($resourceClass) implements QueryBuilderExtensionInterface {
            private string $resourceClass;

            public function __construct(string $resourceClass)
            {
                $this->resourceClass = $resourceClass;
            }

            public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, QueryContext $context)
            {
                $context->setAlias($this->resourceClass, 'bar', 'foo_bar_alias');
            }
        };

        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterApplyingEvent::class))
                   ->andReturn();
        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(\Mockery::type(QueryBuilderFilterAppliedEvent::class))
                   ->andReturn();

        $filterService = new QueryBuilderFilterService($registry, $dispatcher, [$extension]);
        $filterService->filter($resourceClass, $context, $qb);

        self::assertNull(QueryBuilderFilterService::getJoinAlias($resourceClass, 'bam'));
        self::assertEquals('foo_bar_alias', QueryBuilderFilterService::getJoinAlias($resourceClass, 'bar'));
    }

    public function testItThrowExceptionIfResourceIsNotADoctrineEntity()
    {
        $resourceClass = 'Foo\\Bar\\Resource';
        $context       = [
            'filter' => ['foo.bar' => 10],
        ];

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
                 ->once()
                 ->with($resourceClass)
                 ->andReturnNull();

        $extension = \Mockery::mock(QueryBuilderExtensionInterface::class);

        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $filterService = new QueryBuilderFilterService($registry, $dispatcher, [$extension]);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf('Unable to find entity manager for %s class. maybe it is not an entity.', $resourceClass)
        );

        $filterService->filter($resourceClass, $context);
    }
}
