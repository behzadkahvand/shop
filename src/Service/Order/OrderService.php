<?php

namespace App\Service\Order;

use App\Dictionary\OrderStatus;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\PromotionCoupon;
use App\Events\Order\OrderRegisteredEvent;
use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Service\Pipeline\Pipeline;
use App\Service\Promotion\PromotionProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private EventDispatcherInterface $dispatcher,
        private iterable $stages,
        private PromotionProcessorInterface $promotionProcessor,
        private MessageBusInterface $messageBus
    ) {
    }

    /**
     * @throws Throwable
     */
    public function store(
        Customer $customer,
        string $paymentMethod,
        array $shipments,
        CustomerAddress $customerAddress,
        array $affiliatorData = [],
        bool $isLegal = false,
        bool $useWallet = false
    ): Order {
        $cart = $customer->getCartOrFail();

        $pipeline = Pipeline::fromStages($this->stages);
        $payload  = new CreateOrderPayload(
            $this->manager,
            $cart,
            $customerAddress,
            $paymentMethod,
            $shipments,
            $affiliatorData,
            $isLegal,
            $useWallet
        );

        $this->manager->beginTransaction();

        try {
            $order = $pipeline->process($payload)->getOrder();

            $this->manager->persist($order);
            $this->manager->flush();
            $this->manager->commit();
        } catch (Throwable $exception) {
            $this->manager->close();
            $this->manager->rollback();

            throw $exception;
        }

        if ($order->getAffiliator() && $order->getStatus() === OrderStatus::WAIT_CUSTOMER) {
            $message = new SendOrderAffiliatorPurchaseRequest($order->getId());

            $this->messageBus->dispatch($message);
        }

        $this->dispatcher->dispatch(new OrderRegisteredEvent($order));

        return $order;
    }

    public function setPromotionCoupon(Order $order, ?PromotionCoupon $promotionCoupon)
    {
        $order->setPromotionCoupon($promotionCoupon);
        $this->promotionProcessor->process($order);
    }
}
