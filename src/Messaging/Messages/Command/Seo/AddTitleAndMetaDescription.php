<?php

namespace App\Messaging\Messages\Command\Seo;

class AddTitleAndMetaDescription
{
    public function __construct(private int $categoryId)
    {
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
