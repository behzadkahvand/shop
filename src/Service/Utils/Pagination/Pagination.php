<?php

namespace App\Service\Utils\Pagination;

/**
 * Class Pagination
 */
final class Pagination
{
    private int $page;

    private int $limit;

    public function __construct(int $page = 1, int $limit = 20)
    {
        $this->setPage($page);
        $this->setLimit($limit);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage($page): self
    {
        $this->page  = 0 < $page ? abs($page) : 1;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit($limit): self
    {
        $this->limit = 0 < $limit ? abs($limit) : 20;

        return $this;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
