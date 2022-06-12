<?php

namespace App\Service\ORM\CustomFilters;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface CustomFilterInterface
 */
interface CustomFilterInterface
{
    /**
     * @param Request $request
     */
    public function apply(Request $request): void;
}
