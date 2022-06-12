<?php

namespace App\Service\ORM;

use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\Extension\QueryBuilderExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class QueryBuilderFilterService.
 */
class QueryBuilderFilterService
{
    /**
     * @var array
     */
    private static array $joinMap = [];

    /**
     * @var QueryBuilderExtensionInterface[]
     */
    private iterable $collectionExtensions;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $registry;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * QueryBuilderFilterService constructor.
     *
     * @param ManagerRegistry $registry
     * @param EventDispatcherInterface $dispatcher
     * @param QueryBuilderExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
        iterable $collectionExtensions
    ) {
        $this->registry             = $registry;
        $this->dispatcher           = $dispatcher;
        $this->collectionExtensions = $collectionExtensions;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $resourceClass
     * @param array $context
     *
     * @return QueryBuilder
     */
    public function filter(string $resourceClass, array $context = [], QueryBuilder $queryBuilder = null): QueryBuilder
    {
        if (!$queryBuilder) {
            $queryBuilder = $this->createNewQueryBuilder($resourceClass);
        }

        if (!$context) {
            return $queryBuilder;
        }

        $aliases = $queryBuilder->getRootAliases();
        $alias   = array_shift($aliases);
        $context = new QueryContext($context, $alias, \Closure::fromCallable([$this, 'addJoin']));

        $this->resetJoinMap();

        $event = new QueryBuilderFilterApplyingEvent($queryBuilder, $context, $alias);
        $this->dispatcher->dispatch($event);

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $resourceClass, $context);
        }

        $event = new QueryBuilderFilterAppliedEvent($queryBuilder, $context, $alias, self::$joinMap);
        $this->dispatcher->dispatch($event);

        return $queryBuilder;
    }

    /**
     * @return array
     */
    public static function getJoinMap(): array
    {
        return self::$joinMap;
    }

    /**
     * @param array $joinMap
     */
    public static function setJoinMap(array $joinMap): void
    {
        self::$joinMap = $joinMap;
    }

    /**
     * @param string $fromEntity
     * @param string $toEntity
     *
     * @return string|null
     */
    public static function getJoinAlias(string $fromEntity, string $toEntity): ?string
    {
        return self::$joinMap[$fromEntity][$toEntity] ?? null;
    }

    /**
     * @param string $entity
     * @param string $association
     * @param string $alias
     */
    private function addJoin(string $entity, string $association, string $alias): void
    {
        if (!isset(self::$joinMap[$entity][$association])) {
            self::$joinMap[$entity][$association] = $alias;
        }
    }

    /**
     * @return void
     */
    private function resetJoinMap(): void
    {
        self::$joinMap = [];
    }

    /**
     * @param string $resourceClass
     *
     * @return mixed
     */
    private function createNewQueryBuilder(string $resourceClass)
    {
        $em                 = $this->getEntityManagerForResource($resourceClass);
        $class              = basename(str_replace('\\', DIRECTORY_SEPARATOR, $resourceClass));
        $resourceClassAlias = snake_case($class . substr(md5(microtime(true)), 0, 3));

        return $em->getRepository($resourceClass)->createQueryBuilder($resourceClassAlias);
    }

    /**
     * @param string $resourceClass
     *
     * @return ObjectManager
     */
    private function getEntityManagerForResource(string $resourceClass): ObjectManager
    {
        $em = $this->registry->getManagerForClass($resourceClass);

        if (null === $em) {
            throw new InvalidArgumentException(
                sprintf('Unable to find entity manager for %s class. maybe it is not an entity.', $resourceClass)
            );
        }

        return $em;
    }
}
