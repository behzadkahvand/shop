<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use App\Service\Layout\CacheBlock\CacheOnSaleBlock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OnSaleBlockCompilerPass implements CompilerPassInterface
{
    private bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition(CacheOnSaleBlock::class);

        if ($this->debug) {
            return;
        }

        foreach ($container->findTaggedServiceIds('app.layout.on_sale.block') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $reflectionClass = $container->getReflectionClass($definition->getClass());
            if (!$reflectionClass->implementsInterface(CacheableBlockInterface::class)) {
                continue;
            }
            $decoratorDefinition = new Definition(CacheOnSaleBlock::class);
            $decoratorDefinition->setDecoratedService($id)
                ->setArgument('$decorated', new Reference("$id.Decorator.inner"))
                ->setAutowired(true)
                ->setAutoconfigured(true);
            $container->setDefinition("$id.Decorator", $decoratorDefinition);
        }
    }
}
