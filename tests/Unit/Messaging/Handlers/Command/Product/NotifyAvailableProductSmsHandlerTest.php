<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Messaging\Handlers\Command\Product\NotifyAvailableProductSmsHandler;
use App\Messaging\Messages\Command\Product\NotifyAvailableProduct;
use App\Repository\ProductNotifyRequestRepository;
use App\Service\Notification\DTOs\Customer\Product\NotifyAvailableSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Product\NotifyMe\NotifyMeService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class NotifyAvailableProductSmsHandlerTest extends BaseUnitTestCase
{
    private NotificationService|LegacyMockInterface|MockInterface|null $notificationServiceMock;

    private NotifyMeService|LegacyMockInterface|MockInterface|null $notifyMeServiceMock;

    private LegacyMockInterface|ProductNotifyRequestRepository|MockInterface|null $notifyRequestRepoMock;

    private ?NotifyAvailableProductSmsHandler $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->notifyRequestRepoMock   = Mockery::mock(ProductNotifyRequestRepository::class);
        $this->notifyMeServiceMock     = Mockery::mock(NotifyMeService::class);

        $this->sut = new NotifyAvailableProductSmsHandler(
            $this->notificationServiceMock,
            $this->notifyRequestRepoMock,
            $this->notifyMeServiceMock
        );
    }

    public function testDoNothingIfNotFoundRequest(): void
    {
        $this->notifyRequestRepoMock->shouldReceive('findBy')
                                    ->with(['product' => 10])
                                    ->once()
                                    ->andReturn([]);

        $this->notificationServiceMock->shouldNotReceive('send');
        $this->notifyMeServiceMock->shouldNotReceive('removeRequest');

        ($this->sut)(new NotifyAvailableProduct(10));
    }

    public function testSendNotifyViaSmsSuccessfully(): void
    {
        $productId     = 10;
        $notifyRequest = Mockery::mock(ProductNotifyRequest::class);
        $productMock   = Mockery::mock(Product::class);
        $customerMock  = Mockery::mock(Customer::class);

        $notifyRequest->shouldNotReceive('getProduct')
                      ->twice()
                      ->withNoArgs()
                      ->andReturn($productMock);

        $notifyRequest->shouldNotReceive('getCustomer')
                      ->twice()
                      ->withNoArgs()
                      ->andReturn($customerMock);

        $this->notifyRequestRepoMock
            ->shouldReceive('findBy')
            ->with(['product' => $productId])
            ->andReturn(
                [
                    $notifyRequest,
                    $notifyRequest,
                ]
            );

        $this->notificationServiceMock->shouldNotReceive('send')
                                      ->twice()
                                      ->with(Mockery::type(NotifyAvailableSmsNotificationDTO::class))
                                      ->andReturn();

        $this->notifyMeServiceMock->shouldReceive('removeRequest')
                                  ->twice()
                                  ->with($customerMock, $productMock)
                                  ->andReturnTrue();

        ($this->sut)(new NotifyAvailableProduct($productId));
    }
}
