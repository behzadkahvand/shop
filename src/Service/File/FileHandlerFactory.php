<?php

namespace App\Service\File;

use App\Service\File\Exceptions\UnsupportedTypeException;

final class FileHandlerFactory
{
    private iterable $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function create(string $filePath, ?string $presenterModelName = null): FileHandlerInterface
    {
        /** @var \App\Service\File\FileHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->support($filePath, $presenterModelName)) {
                return $handler;
            }
        }

        throw new UnsupportedTypeException('No handler supporting the given file type.');
    }
}
