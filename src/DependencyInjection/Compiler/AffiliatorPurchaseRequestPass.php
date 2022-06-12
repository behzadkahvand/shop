<?php

namespace App\DependencyInjection\Compiler;

use App\Service\OrderAffiliator\OrderAffiliatorFactory;
use App\Service\OrderAffiliator\PurchaseRequest\AffiliatorPurchaseRequestInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AffiliatorPurchaseRequestPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $refMap = [];
        foreach ($container->findTaggedServiceIds('app.order.affiliator_request') as $id => $tags) {
            $class      = $container->getDefinition($id)->getClass();
            $reflection = $container->getReflectionClass($class);

            if (!$reflection->implementsInterface(AffiliatorPurchaseRequestInterface::class)) {
                throw new \RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as affiliator purchase request',
                    $class,
                    AffiliatorPurchaseRequestInterface::class
                ));
            }

            $refMap[$class] = new Reference($id);
        }

        $container->getDefinition(OrderAffiliatorFactory::class)
                  ->setArgument('$container', ServiceLocatorTagPass::register($container, $refMap));
    }
}
