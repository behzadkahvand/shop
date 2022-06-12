<?php

namespace App\Service\ExceptionHandler;

use Symfony\Contracts\Translation\TranslatorInterface;

interface RenderableThrowableInterface
{
    public function getMetadata(TranslatorInterface $translator): ThrowableMetadata;
}
