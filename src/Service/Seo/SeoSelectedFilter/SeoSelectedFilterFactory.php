<?php

namespace App\Service\Seo\SeoSelectedFilter;

use App\Entity\Brand;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Entity\Seo\SeoSelectedFilter;
use App\Service\Seo\SeoSelectedFilter\Exceptions\InvalidSeoSelectedEntityException;

class SeoSelectedFilterFactory
{
    public function getSeoSelectedFilter($entity): SeoSelectedFilter
    {
        if ($entity instanceof Brand) {
            return new SeoSelectedBrandFilter();
        }

        throw new InvalidSeoSelectedEntityException();
    }
}
