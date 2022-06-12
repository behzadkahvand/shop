<?php

namespace App\DependencyInjection\Compiler;

use App\Service\ExceptionHandler\Factories\AbstractMetadataFactory;
use App\Service\ExceptionHandler\Loaders\MetadataLoaderInterface;
use App\Service\ExceptionHandler\Loaders\StaticListMetadataLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ExceptionMetadataLoaderFactoryPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('app.exception_handler.metadata_loader') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class      = $definition->getClass();
            $reflection = $container->getReflectionClass($class);

            if (!$reflection->implementsInterface(MetadataLoaderInterface::class)) {
                $message = sprintf(
                    'Class %s must implement %s interface to be used as a throwable metadata loader.',
                    $class,
                    MetadataLoaderInterface::class
                );

                throw new \InvalidArgumentException($message);
            }
        }

        $refMap = [];
        foreach ($container->findTaggedServiceIds('app.exception_handler.metadata_factory') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class      = $definition->getClass();
            $reflection = $container->getReflectionClass($class);

            if (!$reflection->isSubclassOf(AbstractMetadataFactory::class)) {
                $message = sprintf(
                    'Class %s must extend %s class to be used as a throwable metadata factory.',
                    $class,
                    AbstractMetadataFactory::class
                );

                throw new \InvalidArgumentException($message);
            }

            $refMap[$class] = new Reference($id);
        }

        $container->getDefinition(StaticListMetadataLoader::class)
                  ->setArgument('$container', ServiceLocatorTagPass::register($container, $refMap));
    }
}
