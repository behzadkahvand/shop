<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Holiday\Adapters\CacheHolidayService;
use App\Service\Holiday\Adapters\FridayHolidayServiceAdapter;
use App\Service\Holiday\HolidayServiceFactory;
use App\Service\Holiday\HolidayServiceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class HolidayServiceDriverPass
 */
final class HolidayServiceDriverPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(HolidayServiceInterface::class)) {
            return;
        }

        $drivers = [];
        foreach ($container->findTaggedServiceIds('app.holiday_service_driver') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();

            if (in_array($class, [FridayHolidayServiceAdapter::class, CacheHolidayService::class])) {
                $container->removeDefinition($id);

                continue;
            }

            if (!(new \ReflectionClass($class))->implementsInterface(HolidayServiceInterface::class)) {
                throw new \RuntimeException(sprintf(
                    'Class %s must implement %s interface in order to be used as holiday service driver',
                    $class,
                    HolidayServiceInterface::class
                ));
            }

            $drivers[$class::getName()] = new Reference($id);
        }

        $locator = ServiceLocatorTagPass::register($container, $drivers);

        $container->getDefinition(HolidayServiceFactory::class)->setArguments([$locator, array_keys($drivers)]);

        $container->register(HolidayServiceInterface::class, HolidayServiceInterface::class)
                  ->setArguments(['%holiday_service_driver%'])
                  ->setFactory([new Reference(HolidayServiceFactory::class), 'create']);
    }
}
