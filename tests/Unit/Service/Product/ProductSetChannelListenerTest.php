<?php

namespace App\Tests\Unit\Service\Product;

use App\Dictionary\ProductChannelDictionary;
use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Seller;
use App\Service\Product\ProductSetChannelListener;
use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Security;
use Mockery as m;

final class ProductSetChannelListenerTest extends MockeryTestCase
{
    private $product;

    private $security;

    public function testDontSetChannelWhenUserIsNotLogin()
    {
        $this->security
            ->shouldReceive('getUser')
            ->andReturnNull();

        $listener = new ProductSetChannelListener($this->security);

        $listener->onProductPrePersist($this->product);
    }

    public function testSetChannelWhenUserIsInstanceOfAdmin(): void
    {
        $this->security
            ->shouldReceive('getUser')
            ->andReturn(new Admin());

        $this->product
            ->shouldReceive('setChannel')
            ->with(ProductChannelDictionary::ADMIN);

        $listener = new ProductSetChannelListener($this->security);

        $listener->onProductPrePersist($this->product);
    }

    public function testSetChannelWhenUserIsInstanceOfSeller(): void
    {
        $this->security
            ->shouldReceive('getUser')
            ->andReturn(new Seller());

        $this->product
            ->shouldReceive('setChannel')
            ->with(ProductChannelDictionary::SELLER);

        $listener = new ProductSetChannelListener($this->security);

        $listener->onProductPrePersist($this->product);
    }

    public function testDontSupportChannelException(): void
    {
        $this->security
            ->shouldReceive('getUser')
            ->andReturn(new Customer());

        $listener = new ProductSetChannelListener($this->security);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The channel is not supported');

        $listener->onProductPrePersist($this->product);
    }


    protected function mockeryTestTearDown()
    {
        m::close();
    }

    protected function mockeryTestSetUp()
    {
        $this->product  = m::mock(Product::class);
        $this->security = m::mock(Security::class);
    }
}
