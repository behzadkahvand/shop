<?php

namespace App\Tests\Unit\Service\CustomerAddress;

use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Service\CustomerAddress\DefaultCustomerAddressService;
use App\Service\CustomerAddress\Exceptions\UnexpectedCustomerAddressException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class DefaultCustomerAddressServiceTest extends MockeryTestCase
{
    protected DefaultCustomerAddressService $defaultCustomerAddress;

    private LegacyMockInterface|MockInterface|EntityManager|null $em;

    private LegacyMockInterface|CustomerAddress|MockInterface|null $customerAddressMock;

    private LegacyMockInterface|MockInterface|Customer|null $customerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManager::class);

        $this->customerAddressMock = Mockery::mock(CustomerAddress::class);

        $this->customerMock = Mockery::mock(Customer::class);

        $this->defaultCustomerAddress = new DefaultCustomerAddressService($this->em);
    }

    protected function tearDown(): void
    {
        unset($this->defaultCustomerAddress);

        $this->em = null;
        $this->customerMock = null;
        $this->customerAddressMock = null;
    }

    public function testItThrowsExceptionWhenCustomerAddressIsNotForCustomer()
    {
        $this->customerMock->shouldReceive('getAddresses')->withNoArgs()->once()
            ->andReturn(new ArrayCollection([$this->customerAddressMock, $this->customerAddressMock, $this->customerAddressMock]));
        $this->customerMock->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1, 2);

        $this->customerAddressMock->shouldReceive('setIsDefault')->times(3)->with(false)->andReturnSelf();
        $this->customerAddressMock->shouldReceive('getCustomer')->once()->andReturn($this->customerMock);

        $this->expectException(UnexpectedCustomerAddressException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Customer address is unexpected!');

        $this->defaultCustomerAddress->set($this->customerMock, $this->customerAddressMock);
    }

    public function testItCanSetNewDefaultCustomerAddress()
    {
        $this->customerMock->shouldReceive('getAddresses')->withNoArgs()->once()
                           ->andReturn(new ArrayCollection([$this->customerAddressMock, $this->customerAddressMock, $this->customerAddressMock]));
        $this->customerMock->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1);

        $this->customerAddressMock->shouldReceive('setIsDefault')->once()->with(false)->andReturnSelf();
        $this->customerAddressMock->shouldReceive('setIsDefault')->once()->with(false)->andReturnSelf();
        $this->customerAddressMock->shouldReceive('setIsDefault')->once()->with(false)->andReturnSelf();
        $this->customerAddressMock->shouldReceive('setIsDefault')->once()->with(true)->andReturnSelf();
        $this->customerAddressMock->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customerMock);

        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->defaultCustomerAddress->set($this->customerMock, $this->customerAddressMock);
    }
}
