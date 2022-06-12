<?php

namespace App\Service\Order\Apology;

use App\Entity\Order;
use App\Exceptions\Apology\FailedToFindApologyForCancelReasonException;
use App\Repository\OrderCancelReasonApologyRepository;
use App\Repository\OrderCancelReasonOrderRepository;
use App\Service\Apology\ApologyService;

class OrderCancellationApologyService
{
    private OrderCancelReasonOrderRepository $cancelReasonOrderRepository;
    private ApologyService $apologyService;
    private OrderCancelReasonApologyRepository $cancelReasonApologyRepository;

    /**
     * OrderCancellationApologyService constructor.
     * @param OrderCancelReasonOrderRepository $cancelReasonOrderRepository
     * @param ApologyService $apologyService
     * @param OrderCancelReasonApologyRepository $cancelReasonApologyRepository
     */
    public function __construct(
        OrderCancelReasonOrderRepository $cancelReasonOrderRepository,
        OrderCancelReasonApologyRepository $cancelReasonApologyRepository,
        ApologyService $apologyService
    ) {
        $this->cancelReasonOrderRepository = $cancelReasonOrderRepository;
        $this->cancelReasonApologyRepository = $cancelReasonApologyRepository;
        $this->apologyService = $apologyService;
    }

    /**
     * @param Order $order
     * @throws FailedToFindApologyForCancelReasonException
     */
    public function apologize(Order $order): void
    {
        $orderCancelReason = $this->cancelReasonOrderRepository->findOneBy(['order' => $order]);
        if (null === $orderCancelReason) {
            return;
        }

        $apology = $this->cancelReasonApologyRepository->findApologyByCancelReason($orderCancelReason->getCancelReason());
        if (null === $apology) {
            throw new FailedToFindApologyForCancelReasonException();
        }

        $this->apologyService->apologize([$order->getCustomer()], $apology);
    }
}
