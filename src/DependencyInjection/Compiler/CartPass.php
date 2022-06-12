<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Cart\Condition\CartConditionInterface;
use App\Service\Cart\Condition\CartConditionsAggregator;
use App\Service\Cart\Processor\CartProcessorAggregator;
use App\Service\Cart\Processor\CartProcessorInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CartPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('app.cart.conditions') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();

            if (! (new ReflectionClass($class))->implementsInterface(CartConditionInterface::class)) {
                throw new RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as Cart Condition',
                    $class,
                    CartConditionInterface::class
                ));
            }

            if ($class === CartConditionsAggregator::class) {
                $container->getDefinition($id)->clearTag('app.cart.conditions');
            }
        }

        foreach ($container->findTaggedServiceIds('app.cart.processor') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();

            if (! (new ReflectionClass($class))->implementsInterface(CartProcessorInterface::class)) {
                throw new RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as Cart Processor',
                    $class,
                    CartProcessorInterface::class
                ));
            }

            if ($class === CartProcessorAggregator::class) {
                $container->getDefinition($id)->clearTag('app.cart.processor');
            }
        }
    }
}
