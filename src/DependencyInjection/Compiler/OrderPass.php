<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Order\Condition\OrderConditionInterface;
use App\Service\Order\Condition\OrderConditionsAggregator;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('app.order.conditions') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $reflection = $container->getReflectionClass($class);

            if (! $reflection->implementsInterface(OrderConditionInterface::class)) {
                throw new RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as Order Condition',
                    $class,
                    OrderConditionInterface::class
                ));
            }

            if ($class === OrderConditionsAggregator::class) {
                $definition->clearTag('app.order.conditions');
            }
        }
    }
}
