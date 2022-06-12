<?php

namespace App\Service\Utils\Error;

/**
 * Interface ErrorExtractorInterface
 */
interface ErrorExtractorInterface
{
    /**
     * @param $errors
     *
     * @return bool
     */
    public function support($errors): bool;

    /**
     * @param $errors
     *
     * @return iterable
     */
    public function extract($errors): iterable;
}
