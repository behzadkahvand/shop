<?php

namespace App\Service\Order\Stages;

use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Exceptions\Order\InvalidOrderStatusException;
use App\Service\Order\OrderIdentifierService;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use DateTime;

class StoreOrderStage implements TagAwarePipelineStageInterface
{
    public function __construct(protected OrderIdentifierService $identifierService)
    {
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        $cart            = $payload->getCart();
        $customerAddress = $payload->getCustomerAddress();
        $paymentMethod   = $payload->getPaymentMethod();
        $isLegal         = $payload->isLegal();
        $manager         = $payload->getEntityManager();

        $customer = $customerAddress->getCustomer();
        $order    = (new Order())
            ->setCustomer($customer)
            ->setGrandTotal($cart->getGrandTotal())
            ->setSubtotal($cart->getSubtotal())
            ->setPaymentMethod($paymentMethod)
            ->setBalanceAmount(0)
            ->setIsLegal($isLegal);

        $orderDocument = (new OrderDocument())
            ->setCompletedAt(new DateTime())
            ->setAmount($order->getGrandTotal())
            ->setOrder($order);

        $order->setOrderDocument($orderDocument);

        $manager->persist($orderDocument);
        $manager->persist($order);
        $manager->flush();

        $order->setIdentifier(
            $this->identifierService->generateIdentifier($order)
        );

        $payload->setOrder($order);

        return $payload;
    }

    public static function getPriority(): int
    {
        return 100;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
