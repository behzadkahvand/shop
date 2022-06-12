<?php

namespace App\DependencyInjection\Compiler;

use App\ParamConverter\CustomFilterParamConverter;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CustomFilterPass
 */
final class CustomFilterPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $refMap = [];
        foreach ($container->findTaggedServiceIds('app.query_builder_filter_service.custom_filter') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();
            $reflection = $container->getReflectionClass($class);

            if (!$reflection->implementsInterface(CustomFilterInterface::class)) {
                throw new \RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as query builder filter custom filter',
                    $class,
                    CustomFilterInterface::class
                ));
            }

            $refMap[$class] = new Reference($id);
        }

        $container->getDefinition(CustomFilterParamConverter::class)
                  ->setArgument('$container', ServiceLocatorTagPass::register($container, $refMap));
    }
}
