<?php

namespace App\Service\Product\Search\Queries;

/**
 * Class AbstractSearchQuery
 */
abstract class AbstractSearchQuery
{
    /**
     * @return iterable
     */
    abstract public function getResult(): iterable;

    abstract public function getResultQuery();

    /**
     * @return array
     */
    abstract public function getMeta(): array;
}
