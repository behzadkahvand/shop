<?php

namespace App\Service\ORM\Extension\Join;

/**
 * Class QueryJoinerData
 */
final class QueryJoinerData
{
    /**
     * @var string
     */
    private string $entityAlias;

    /**
     * @var string
     */
    private string $relationField;

    /**
     * @var string
     */
    private string $entityClass;

    /**
     * @var string
     */
    private string $relationClass;

    /**
     * QueryJoinerData constructor.
     *
     * @param string $entityAlias
     * @param string $relationField
     * @param string $entityClass
     * @param string $relationClass
     */
    public function __construct(string $entityAlias, string $relationField, string $entityClass, string $relationClass)
    {
        $this->entityAlias   = $entityAlias;
        $this->relationField = $relationField;
        $this->entityClass   = $entityClass;
        $this->relationClass = $relationClass;
    }

    /**
     * @return string
     */
    public function getEntityAlias(): string
    {
        return $this->entityAlias;
    }

    /**
     * @return string
     */
    public function getRelationField(): string
    {
        return $this->relationField;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getRelationClass(): string
    {
        return $this->relationClass;
    }
}
