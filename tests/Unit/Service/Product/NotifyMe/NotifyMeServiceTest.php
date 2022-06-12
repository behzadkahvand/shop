<?php

namespace App\Tests\Unit\Service\Product\NotifyMe;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Repository\ProductNotifyRequestRepository;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestAlreadyExistsException;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestNotFoundException;
use App\Service\Product\NotifyMe\NotifyMeService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class NotifyMeServiceTest extends MockeryTestCase
{
    /** @var ProductNotifyRequestRepository|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    private $repository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $manager;

    private Product $product;

    private Customer $customer;

    private ProductNotifyRequest $notifyRequest;

    public function testRequestAlreadyExists(): void
    {
        $this->expectException(NotifyRequestAlreadyExistsException::class);
        $this->repository
            ->shouldReceive('findCustomerProductNotifyRequestOnProduct')
            ->with($this->customer, $this->product)
            ->once()
            ->andReturn($this->notifyRequest);

        $notifyMeService = new NotifyMeService($this->repository, $this->manager);
        $notifyMeService->makeRequest($this->notifyRequest);
    }

    public function testMakeANewProductNotifyRequest(): void
    {
        $this->repository
            ->shouldReceive('findCustomerProductNotifyRequestOnProduct')
            ->with($this->customer, $this->product)
            ->once()
            ->andReturnNull();

        $this->manager->shouldReceive('persist')
                      ->once()
                      ->with($this->notifyRequest)
                      ->andReturn();

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $notifyMeService = new NotifyMeService($this->repository, $this->manager);

        $this->assertSame($this->notifyRequest, $notifyMeService->makeRequest($this->notifyRequest));
    }

    public function testRemoveAProductNotifyRequest(): void
    {
        $this->repository
            ->shouldReceive('findCustomerProductNotifyRequestOnProduct')
            ->with($this->customer, $this->product)
            ->once()
            ->andReturn($this->notifyRequest);

        $this->manager->shouldReceive('remove')
                      ->once()
                      ->with($this->notifyRequest)
                      ->andReturn();

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $notifyMeService = new NotifyMeService($this->repository, $this->manager);
        $this->assertTrue($notifyMeService->removeRequest($this->customer, $this->product));
    }

    public function testExistsProductNotifyRequest(): void
    {
        $this->repository
            ->shouldReceive('findCustomerProductNotifyRequestOnProduct')
            ->with($this->customer, $this->product)
            ->once()
            ->andReturn($this->notifyRequest);

        $notifyMeService = new NotifyMeService($this->repository, $this->manager);
        $notifyMeService->existsRequest($this->customer, $this->product);
    }

    public function testRemoveRequestNotFoundException(): void
    {
        $this->expectException(NotifyRequestNotFoundException::class);

        $this->repository
            ->shouldReceive('findCustomerProductNotifyRequestOnProduct')
            ->with($this->customer, $this->product)
            ->once()
            ->andReturnNull();

        $notifyMeService = new NotifyMeService($this->repository, $this->manager);
        $notifyMeService->removeRequest($this->customer, $this->product);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->product  = new Product();
        $this->customer = new Customer();

        $this->notifyRequest = (new ProductNotifyRequest())
            ->setCustomer($this->customer)
            ->setProduct($this->product);

        $this->repository = m::mock(ProductNotifyRequestRepository::class);
        $this->manager    = m::mock(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->product, $this->customer, $this->notifyRequest);
        m::close();
    }
}
