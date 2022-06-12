<?php

namespace App\Service\ExceptionHandler;

use App\Service\ExceptionHandler\Loaders\InternalServerErrorMetadataLoader;
use App\Service\ExceptionHandler\Loaders\MetadataLoaderInterface;
use Throwable;

class MetadataLoader
{
    private iterable $metadataLoaders;

    private MetadataLoaderInterface $fallbackLoader;

    public function __construct(iterable $metadataLoaders, MetadataLoaderInterface $fallbackLoader = null)
    {
        $this->metadataLoaders = $metadataLoaders;
        $this->fallbackLoader = $fallbackLoader ?? new InternalServerErrorMetadataLoader();
    }

    public function getMetadata(Throwable $throwable): ThrowableMetadata
    {
        foreach ($this->metadataLoaders as $metadataLoader) {
            if ($metadataLoader->supports($throwable)) {
                return $metadataLoader->load($throwable);
            }
        }

        return $this->fallbackLoader->load($throwable);
    }
}
