<?php

namespace App\Service\Cart;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Repository\CartRepository;
use App\Service\Cart\Exceptions\CartNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class CartStorageService
{
    protected Security $security;

    protected RequestStack $requestStack;

    protected CartRepository $cartRepository;

    protected EntityManagerInterface $manager;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        CartRepository $cartRepository
    ) {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->cartRepository = $cartRepository;
    }

    public function getCart(): Cart
    {
        if ($this->userIsAuthenticated()) {
            /** @var Customer $user */
            $user = $this->security->getUser();

            return $user->getCart() ?? $this->createCart($user);
        }

        if ($this->headerHasCartId()) {
            $cart = $this->cartRepository->findOneBy([
                'id' => $this->getCartIdFromHeaders(),
                'customer' => null,
            ]);

            return $cart ?? $this->createCart();
        }

        return $this->createCart();
    }

    public function findCartOrFail(): Cart
    {
        if ($this->userIsAuthenticated()) {
            /** @var Customer $user */
            $user = $this->security->getUser();

            $cart = $user->getCart();

            if ($cart === null) {
                throw new CartNotFoundException();
            }

            return $user->getCart();
        }

        if ($this->headerHasCartId()) {
            return $this->cartRepository->findOrFail($this->getCartIdFromHeaders());
        }

        throw new CartNotFoundException();
    }

    private function userIsAuthenticated(): bool
    {
        return $this->security->getUser() !== null;
    }

    private function headerHasCartId(): bool
    {
        return $this->requestStack->getCurrentRequest()->headers->has('X-Cart');
    }

    private function getCartIdFromHeaders(): ?string
    {
        return $this->requestStack->getCurrentRequest()->headers->get('X-Cart');
    }

    private function createCart(Customer $user = null): Cart
    {
        $cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        if ($this->userIsAuthenticated()) {
            $cart->setCustomer($user);

            $this->manager->persist($cart);
            $this->manager->flush();
        }


        return $cart;
    }
}
