<?php

namespace App\Service\Product\Search;

class DoctrineSearchData extends SearchData
{
    private ?string $categoryCode;

    private ?string $title;

    public function __construct(array $filters, array $sorts, string $categoryCode = null, string $title = null)
    {
        parent::__construct($filters, $sorts);

        $this->categoryCode = $categoryCode;
        $this->title        = $title;
    }

    /**
     * @return string|null
     */
    public function getCategoryCode(): ?string
    {
        return $this->categoryCode;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }
}
