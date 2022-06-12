<?php

namespace App\Service\Utils\Error\Adapters;

use App\Service\Utils\Error\ErrorExtractorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ValidationViolationErrorExtractor
 */
final class ValidationViolationErrorExtractor implements ErrorExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function support($errors): bool
    {
        return $errors instanceof ConstraintViolationListInterface;
    }

    /**
     * @inheritDoc
     */
    public function extract($errors): iterable
    {
        foreach ($errors as $error) {
            yield trim(preg_replace('/[\]\[]+/', '.', $error->getPropertyPath()), '.') => [$error->getMessage()];
        }
    }
}
