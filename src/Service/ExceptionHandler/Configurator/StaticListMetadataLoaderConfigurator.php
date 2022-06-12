<?php

namespace App\Service\ExceptionHandler\Configurator;

use App\Service\ExceptionHandler\Loaders\StaticListMetadataLoader;

final class StaticListMetadataLoaderConfigurator
{
    private string $metadataFactoryList;

    public function __construct(string $metadataFactoryList)
    {
        $this->metadataFactoryList = $metadataFactoryList;
    }

    public function configure(StaticListMetadataLoader $loader)
    {
        $loader->setFactories(include $this->metadataFactoryList);
    }
}
