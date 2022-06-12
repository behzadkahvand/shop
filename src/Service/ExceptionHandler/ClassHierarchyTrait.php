<?php

namespace App\Service\ExceptionHandler;

use Throwable;

trait ClassHierarchyTrait
{
    public function getClassHierarchy(Throwable $throwable)
    {
        $class = get_class($throwable);

        return [$class => $class] + class_parents($throwable) + class_implements($throwable);
    }
}
