<?php

namespace App\Service\ORM\Extension\Adapter;

use App\Service\ORM\Extension\QueryBuilderExtensionInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Class AbstractQueryBuilderExtension.
 */
abstract class AbstractQueryBuilderExtension implements QueryBuilderExtensionInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var array
     */
    private $loadedMetaData;

    /**
     * AbstractQueryBuilderExtension constructor.
     *
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->loadedMetaData = [];
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    protected function isNested(string $field): bool
    {
        return false !== strpos($field, '.');
    }

    /**
     * @param string $resourceClass
     * @param string $field
     *
     * @return bool
     */
    protected function hasField(string $resourceClass, string $field): bool
    {
        $classMetadata = $this->getClassMetadata($resourceClass);

        return $classMetadata ? $classMetadata->hasField($field) : false;
    }

    /**
     * @param string $resourceClass
     * @param string $association
     *
     * @return bool
     */
    protected function hasAssociation(string $resourceClass, string $association): bool
    {
        $classMetadata = $this->getClassMetadata($resourceClass);

        return $classMetadata ? $classMetadata->hasAssociation($association) : false;
    }

    /**
     * @param string $resourceClass
     * @param string $association
     *
     * @return string|null
     */
    protected function getRelationClass(string $resourceClass, string $association): string
    {
        $classMetadata = $this->getClassMetadata($resourceClass);

        if (!$classMetadata->hasAssociation($association)) {
            throw new \RuntimeException(sprintf('Class %s has no nested relation on %s', $resourceClass, $association));
        }

        return $classMetadata->getAssociationTargetClass($association);
    }

    /**
     * @param string $resourceClass
     *
     * @return ClassMetadata|null
     */
    protected function getClassMetadata(string $resourceClass): ?ClassMetadata
    {
        if (isset($this->loadedMetaData[$resourceClass])) {
            return $this->loadedMetaData[$resourceClass];
        }

        $objectManager = $this->managerRegistry->getManagerForClass($resourceClass);

        if (!$objectManager) {
            return null;
        }

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $objectManager->getClassMetadata($resourceClass);

        return $this->loadedMetaData[$resourceClass] = $classMetadata;
    }
}
