<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Payment\Gateways\COD\GatewayInterface as CODGatewayInterface;
use App\Service\Payment\Gateways\GatewayInterface;
use App\Service\Payment\PaymentHelperService;
use App\Service\Payment\PaymentService;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PaymentGatewayPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $onlineGateways = [];
        $gateways = [];
        $codGateways = [];
        $offlineGateways = [];
        foreach ($container->findTaggedServiceIds('app.payment.online_gateway') as $id => $tags) {
            /** @var GatewayInterface $class */
            $class = $container->getDefinition($id)->getClass();

            if (! is_subclass_of($class, GatewayInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Class %s must implement %s interface in order to be used as online payment gateway.',
                    $class,
                    GatewayInterface::class
                ));
            }

            $gatewayName = $class::getName();
            $onlineGateways[] = $gatewayName;
            $gateways[$gatewayName] = new Reference($id);
        }

        foreach ($container->findTaggedServiceIds('app.payment.cod_gateway') as $id => $tags) {
            /** @var GatewayInterface $class */
            $class = $container->getDefinition($id)->getClass();

            if (! is_subclass_of($class, CODGatewayInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Class %s must implement %s interface in order to be used as COD payment gateway.',
                    $class,
                    CODGatewayInterface::class
                ));
            }

            $gatewayName = $class::getName();
            $codGateways[] = $gatewayName;
            $offlineGateways[$gatewayName] = new Reference($id);
        }

        $paymentServiceDefinition = $container->getDefinition(PaymentService::class);
        $container->setParameter('online_gateways', $onlineGateways);
        $paymentServiceDefinition->setArgument(
            '$gatewayLocator',
            ServiceLocatorTagPass::register($container, $gateways)
        );

        $container->setParameter('cod_gateways', $codGateways);

        $paymentHelperServiceDefinition = $container->getDefinition(PaymentHelperService::class);
        $paymentHelperServiceDefinition->setArgument(
            '$gatewayLocator',
            ServiceLocatorTagPass::register($container, $offlineGateways)
        );
    }
}
