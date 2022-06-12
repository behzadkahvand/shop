<?php

namespace App\EventSubscriber\Cart;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Events\OTP\OtpLoginEvent;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AttachCartToUserListener implements EventSubscriberInterface
{
    protected EventDispatcherInterface $dispatcher;

    protected RequestStack $requestStack;

    protected EntityManagerInterface $manager;

    protected ?Cart $cart = null;

    protected Customer $user;

    protected CartService $cartService;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        CartService $cartService
    ) {
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return ['otp.login' => 'onOtpLogin'];
    }

    public function onOtpLogin(OtpLoginEvent $event): void
    {
        $this->user = $event->getUser();

        if (! $this->headerHasCartId()) {
            return;
        }

        if (! $this->cartIsExists()) {
            return;
        }

        // Attach anonymously created cart to user is the sole purpose of this listener.
        if (! $this->cartCreatedAnonymously()) {
            return;
        }

        if (! $this->userAlreadyHasAnAttachedCart()) {
            $this->attachAnonymousCartToUser();

            return;
        }

        $this->mergeUserAttachedCartWithAnonymousCart();
    }

    private function headerHasCartId(): bool
    {
        return $this->requestStack->getCurrentRequest()->headers->has('X-Cart');
    }

    private function cartIsExists(): bool
    {
        $cart = $this->manager->find(Cart::class, $this->getCartIdFromHeader());

        if ($cart === null) {
            return false;
        }

        $this->cart = $cart;

        return true;
    }

    private function getCartIdFromHeader(): ?string
    {
        return $this->requestStack->getCurrentRequest()->headers->get('X-Cart');
    }

    private function cartCreatedAnonymously(): bool
    {
        return $this->cart->getCustomer() === null;
    }

    private function userAlreadyHasAnAttachedCart(): bool
    {
        return $this->user->getCart() !== null;
    }

    private function attachAnonymousCartToUser(): void
    {
        $this->cart->setCustomer($this->user);
        $this->user->setCart($this->cart);

        $this->manager->flush();
    }

    private function mergeUserAttachedCartWithAnonymousCart(): void
    {
        $this->cartService->mergeCarts($this->user->getCart(), $this->cart);
    }
}
