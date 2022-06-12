<?php

namespace App\Service\Product\Search;

/**
 * Class SearchData
 */
class SearchData
{
    private array $filters;

    private array $sorts;

    public function __construct(array $filters, array $sorts)
    {
        $this->filters = $filters;
        $this->sorts = $sorts;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }
}
