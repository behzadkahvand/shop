<?php

namespace App\Service\Product\Search\Queries\Doctrine;

use App\Service\Product\Search\Queries\AbstractSearchQuery;
use Doctrine\ORM\AbstractQuery;

/**
 * Class QuerySearchQuery
 */
final class QuerySearchQuery extends AbstractSearchQuery
{
    private AbstractQuery $query;

    private array $meta;

    public function __construct(AbstractQuery $query, array $meta = [])
    {
        $this->query = $query;
        $this->meta  = $meta;
    }

    /**
     * @inheritDoc
     */
    public function getResult(): iterable
    {
        return $this->query->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getResultQuery(): AbstractQuery
    {
        return $this->query;
    }
}
