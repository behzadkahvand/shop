<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Notification\SMS\SmsDriverInterface;
use App\Service\Notification\SMS\SmsDriverFactory;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SmsDriverPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(SmsDriverInterface::class)) {
            return;
        }

        $drivers = [];
        foreach ($container->findTaggedServiceIds('app.sms_driver') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();

            if (! (new ReflectionClass($class))->implementsInterface(SmsDriverInterface::class)) {
                throw new RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as SMS Driver',
                    $class,
                    SmsDriverInterface::class
                ));
            }

            $drivers[$class::getName()] = new Reference($id);
        }

        $locator = ServiceLocatorTagPass::register($container, $drivers);

        $container->getDefinition(SmsDriverFactory::class)->setArguments([$locator, array_keys($drivers)]);

        $container->register(SmsDriverInterface::class, SmsDriverInterface::class)
            ->setArguments(['%sms_driver%'])
            ->setFactory([new Reference(SmsDriverFactory::class), 'create']);
    }
}
