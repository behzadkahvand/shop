<?php

namespace App\Tests\Integration\EventSubscriber\Order;

use App\Dictionary\OrderStatus;
use App\Entity\Apology;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderCancelReason;
use App\Entity\OrderCancelReasonApology;
use App\Entity\OrderCancelReasonOrder;
use App\Entity\OrderDocument;
use App\EventSubscriber\Order\OrderStatusChangedSubscriber;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Tests\Integration\BaseIntegrationTestCase;
use App\Tests\TestDoubles\Order\OrderBuilder;
use App\Tests\TestDoubles\Promotion\PromotionBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OrderStatusChangedSubscriberTest extends BaseIntegrationTestCase
{
    private const MESSAGE = 'this is a dummy message';

    private ?string $customerMobile;

    private ?OrderBuilder $orderBuilder;

    private ?PromotionBuilder $promotionBuilder;

    private ?Order $order;

    private ?object $transport;

    private ?EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder     = new OrderBuilder();
        $this->promotionBuilder = new PromotionBuilder();
        $sut                    = $this->getService(
            OrderStatusChangedSubscriber::class
        );
        assert($sut instanceof OrderStatusChangedSubscriber);
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($sut);
        $this->transport = $this->getService('messenger.transport.async');

        $this->seed();
    }

    public function testShouldSendSmsNotification(): void
    {
        $oldStatus  = OrderStatus::WAIT_CUSTOMER;
        $nextStatus = OrderStatus::CANCELED;
        $this->dispatcher->dispatch(
            new OrderStatusChanged($this->order, $oldStatus, $nextStatus)
        );

        self::assertCount(1, $this->transport->get());
        $message = $this->transport->get()[0]->getMessage();
        self::assertInstanceOf(AsyncMessage::class, $message);
        $notification = $message->getWrappedMessage();
        self::assertInstanceOf(SmsNotification::class, $notification);
        self::assertEquals($this->customerMobile, $notification->getRecipient()->getMobile());
        self::assertEquals(self::MESSAGE, $notification->getMessage());
    }

    public function testShouldNotSendNotificationIfNewStatusIsNotCanceledStatus(): void
    {
        $oldStatus  = OrderStatus::WAIT_CUSTOMER;
        $nextStatus = OrderStatus::CONFIRMED;

        $this->dispatcher->dispatch(
            new OrderStatusChanged($this->order, $oldStatus, $nextStatus)
        );

        self::assertCount(0, $this->transport->get());
    }

    private function seed(): void
    {
        $customer             = new Customer();
        $this->customerMobile = (string)rand();
        $customer->setMobile($this->customerMobile)->setIsForeigner(0)->setIsActive(true);

        $doc = new OrderDocument();
        $doc->setAmount(120);

        $this->order = $this->orderBuilder
            ->withCustomer($customer)->withDocument($doc)->build();

        $promotion = $this->promotionBuilder->build();

        $apology = new Apology();
        $apology->setCodePrefix('x')->setMessageTemplate(self::MESSAGE);
        $apology->setPromotion($promotion);

        $cancelReason = (new OrderCancelReason());
        $cancelReason->setCode((string)rand());

        $cancelReasonApology = new OrderCancelReasonApology(
            $cancelReason,
            $apology
        );

        $orderCancelReason = new OrderCancelReasonOrder(
            $this->order,
            $cancelReason
        );

        $this->manager->persist($customer);
        $this->manager->persist($orderCancelReason);
        $this->manager->persist($cancelReasonApology);
        $this->manager->persist($apology);
        $this->manager->flush();
    }
}
