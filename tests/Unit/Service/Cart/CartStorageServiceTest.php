<?php

namespace App\Tests\Unit\Service\Cart;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Repository\CartRepository;
use App\Service\Cart\CartStorageService;
use App\Service\Cart\Exceptions\CartNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class CartStorageServiceTest extends MockeryTestCase
{
    /**
     * @var m\LegacyMockInterface|m\MockInterface|Security
     */
    private $security;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|RequestStack
     */
    private $requestStack;

    /**
     * @var CartRepository|m\LegacyMockInterface|m\MockInterface
     */
    private $cartRepository;

    /**
     * @var EntityManagerInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $manager;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|ContainerInterface
     */
    private $container;

    private Cart $cart;

    private Customer $user;

    private CartStorageService $cartStorageService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $this->user = (new Customer())
            ->setMobile('09121234567');

        $this->container = m::mock(ContainerInterface::class);
        $this->security = new Security($this->container);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);

        $this->requestStack = m::mock(RequestStack::class)->makePartial();
        $this->manager = m::mock(EntityManagerInterface::class);
        $this->cartRepository = m::mock(CartRepository::class);

        $this->cartStorageService = new CartStorageService(
            $this->security,
            $this->requestStack,
            $this->manager,
            $this->cartRepository
        );
    }

    protected function tearDown(): void
    {
        $this->container = null;
        $this->security = null;
        $this->tokenStorage = null;
        $this->requestStack = null;
        $this->manager = null;
        $this->cartRepository = null;

        unset($this->cart, $this->user, $this->cartStorageService);
    }

    public function testItCanReturnsCartIfUserIsAuthenticatedAndAlreadyHasACart(): void
    {
        $this->user->setCart($this->cart);

        $this->container->shouldReceive('get')
            ->twice()
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturn(new TestBrowserToken([], $this->user));

        $cart = $this->cartStorageService->getCart();

        self::assertEquals($cart, $this->cart);
    }

    public function testItCanReturnsCartIfUserIsAuthenticatedAndDontHaveACart(): void
    {
        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturn(new TestBrowserToken([], $this->user));

        $this->manager->shouldReceive('persist')
            ->once()
            ->andReturn();

        $this->manager->shouldReceive('flush')
            ->once()
            ->andReturn();

        $cart = $this->cartStorageService->getCart();

        self::assertEquals($cart->getCustomer(), $this->user);
    }

    public function testItCanReturnsCartIfUserIsGuestAndAlreadyHasACart(): void
    {
        $this->user->setCart($this->cart);

        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturnNull();

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function has(): bool
                        {
                            // Check if header has `cart_id`
                            return true;
                        }
                    };
                }
            });

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function get(): int
                        {
                            // `cart_id` in header
                            return 1;
                        }
                    };
                }
            });

        $this->cartRepository->shouldReceive('findOneBy')
            ->once()
            ->andReturn($this->cart);

        $cart = $this->cartStorageService->getCart();

        self::assertEquals($cart, $this->cart);
    }

    public function testItCanReturnsCartIfUserIsGuestAndDontHaveACart(): void
    {
        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturnNull();

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function has(): bool
                        {
                            // Check if header has `cart_id`
                            return false;
                        }
                    };
                }
            });

//        $this->manager->shouldReceive('persist')
//            ->once()
//            ->andReturn();
//
//        $this->manager->shouldReceive('flush')
//            ->once()
//            ->andReturn();

        $cart = $this->cartStorageService->getCart();

        self::assertEquals($cart, $this->cart);
    }

    public function testItCanReturnsCartOrFailsIfUserIsAuthenticatedAndAlreadyHasACart(): void
    {
        $this->user->setCart($this->cart);

        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturn(new TestBrowserToken([], $this->user));

        $cart = $this->cartStorageService->findCartOrFail();

        self::assertEquals($cart, $this->cart);
    }

    public function testItFailsIfUserIsAuthenticatedAndDontHaveACart(): void
    {
        $this->expectException(CartNotFoundException::class);

        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturn(new TestBrowserToken([], $this->user));

        $this->cartStorageService->findCartOrFail();
    }

    public function testItCanReturnsCartOrFailsIfUserIsGuestAndAlreadyHasACart(): void
    {
        $this->user->setCart($this->cart);

        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturnNull();

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function has(): bool
                        {
                            // Check if header has `cart_id`
                            return true;
                        }
                    };
                }
            });

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function get(): int
                        {
                            // `cart_id` in header
                            return 1;
                        }
                    };
                }
            });

        $this->cartRepository->shouldReceive('findOrFail')
            ->once()
            ->with(1) // `cart_id`
            ->andReturn($this->cart);

        $cart = $this->cartStorageService->findCartOrFail();

        self::assertEquals($cart, $this->cart);
    }

    public function testItFailsIfUserIsGuestAndCartIdIsNotExistedInCookie(): void
    {
        $this->expectException(CartNotFoundException::class);

        $this->container->shouldReceive('get')
            ->with('security.token_storage')
            ->andReturn($this->tokenStorage);

        $this->tokenStorage->shouldReceive('getToken')
            ->withNoArgs()
            ->andReturnNull();

        $this->requestStack->shouldReceive('getCurrentRequest')
            ->once()
            ->with()
            ->andReturn(new class () {
                public $headers;

                public function __construct()
                {
                    $this->headers = new class () {
                        public function has(): bool
                        {
                            // Check if header has `cart_id`
                            return false;
                        }
                    };
                }
            });

        $this->cartStorageService->findCartOrFail();
    }
}
