<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Notification\DTOs\AbstractNotificationDTO;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EditableNotificationPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $notifications = [];

        foreach ($container->findTaggedServiceIds('app.editable_notification') as $id => $tags) {
            /** @var AbstractNotificationDTO $class */
            $class = $container->getDefinition($id)->getClass();

            if (! is_subclass_of($class, AbstractNotificationDTO::class)) {
                throw new RuntimeException(sprintf(
                    'Class %s must extend %s abstract class in order to be used as a editable notification listener',
                    $class,
                    AbstractNotificationDTO::class
                ));
            }

            $notifications[$class::getSection()][] = [
                'code' => $class::getCode(),
                'variables' => $class::getVariablesDescription(),
            ];
        }

        $container->setParameter('editable_notifications', $notifications);
    }
}
