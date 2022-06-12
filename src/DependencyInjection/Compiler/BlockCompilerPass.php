<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use App\Service\Layout\CacheBlock\CacheBlock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BlockCompilerPass implements CompilerPassInterface
{
    private bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition(CacheBlock::class);

        if ($this->debug) {
            return;
        }

        foreach ($container->findTaggedServiceIds('app.layout.block') as $id => $tags) {
            $definition      = $container->getDefinition($id);
            $reflectionClass = $container->getReflectionClass($definition->getClass());

            if (!$reflectionClass->implementsInterface(CacheableBlockInterface::class)) {
                continue;
            }

            $decoratorDefinition = new Definition(CacheBlock::class);
            $decoratorDefinition->setDecoratedService($id)
                                ->setArgument('$decorated', new Reference("$id.Decorator.inner"))
                                ->setAutowired(true)
                                ->setAutoconfigured(true);

            $container->setDefinition("$id.Decorator", $decoratorDefinition);
        }
    }
}
