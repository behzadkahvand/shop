<?php

namespace App\EventSubscriber;

use App\Controller\Lendo\LendoTokenAuthenticatedController;
use App\Controller\Monitoring\MonitoringTokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    public function __construct(private array $tokens)
    {
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof LendoTokenAuthenticatedController) {
            $this->validate($event, 'lendo-token', 'lendo');
        }

        if ($controller instanceof MonitoringTokenAuthenticatedController) {
            $this->validate($event, 'monitoring-token', 'monitoring');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    private function validate(ControllerEvent $event, string $requestTokenKey, string $configTokenKey): void
    {
        $token = $event->getRequest()->headers->get($requestTokenKey);
        if ($token !== $this->tokens[$configTokenKey]) {
            throw new AccessDeniedHttpException('This action needs a valid token!');
        }
    }
}
