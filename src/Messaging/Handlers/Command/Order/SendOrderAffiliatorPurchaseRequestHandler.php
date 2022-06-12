<?php

namespace App\Messaging\Handlers\Command\Order;

use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Repository\OrderRepository;
use App\Service\OrderAffiliator\OrderAffiliatorService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendOrderAffiliatorPurchaseRequestHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private OrderRepository $orderRepository,
        private OrderAffiliatorService $orderAffiliatorService
    ) {
    }

    public function __invoke(SendOrderAffiliatorPurchaseRequest $purchaseRequest): void
    {
        $this->logger->debug('affiliator handler');

        $orderId = $purchaseRequest->getOrderId();

        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            $this->logger->error(sprintf('It can not send affiliator purchase request for order %d', $orderId));

            return;
        }

        $this->orderAffiliatorService->purchase($order);
    }
}
