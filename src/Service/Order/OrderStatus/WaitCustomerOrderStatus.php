<?php

namespace App\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Dictionary\SellerPackageType;
use App\Dictionary\ShippingCategoryName;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\SellerPackageFactory;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusService;
use App\Service\Seller\SellerPackage\ValidationStrategy\NullSellerOrderItemValidationStrategy;
use Symfony\Component\Security\Core\Security;

class WaitCustomerOrderStatus extends AbstractOrderStatus
{
    private SellerPackageFactory $packageFactory;

    private SellerPackageStatusService $packageStatusService;

    private SellerOrderItemStatusService $sellerOrderItemStatusService;

    private Security $security;

    public function __construct(
        OrderShipmentStatusService $orderShipmentStatusService,
        SellerPackageFactory $packageFactory,
        SellerPackageStatusService $packageStatusService,
        SellerOrderItemStatusService $sellerOrderItemStatusService,
        Security $security
    ) {
        parent::__construct($orderShipmentStatusService);

        $this->packageFactory               = $packageFactory;
        $this->packageStatusService         = $packageStatusService;
        $this->security                     = $security;
        $this->sellerOrderItemStatusService = $sellerOrderItemStatusService;
    }

    public function callFailed(Order $order): void
    {
        $order->setStatus(OrderStatus::CALL_FAILED);
    }

    public function confirmed(Order $order): void
    {
        $order->setStatus(OrderStatus::CONFIRMED);

        $this->changeShipmentStatuses($order, OrderShipmentStatus::WAITING_FOR_SUPPLY);
        $this->createAndReceivePackageForItemsInWarehouse($order);
    }

    public function support(string $status): bool
    {
        return OrderStatus::WAIT_CUSTOMER === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderStatus::CALL_FAILED,
            OrderStatus::CONFIRMED,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ];
    }

    private function createAndReceivePackageForItemsInWarehouse(Order $order): void
    {
        $orderItems = $order->getOrderItems()
                            ->filter(function (OrderItem $oi) {
                                return 0 === $oi->getLeadTime()
                                    && null === $oi->getSellerOrderItem()->getPackageItem()
                                    && $oi->getSellerOrderItem()->isWaitingForSend();
                            })
                            ->getValues();

        if (0 === count($orderItems)) {
            return;
        }

        $validationStrategy = new NullSellerOrderItemValidationStrategy();
        $admin              = $this->security->getUser();

        foreach ($this->getSellerOrderItemsGroupedBySellerAndPackageType($orderItems) as $packageTypeItems) {
            foreach ($packageTypeItems as $item) {
                $package = $this->packageFactory->create(
                    $item['sellerOrderItems'],
                    $item['packageType'],
                    $item['seller'],
                    $validationStrategy,
                    true
                );

                $this->packageStatusService->change($package, SellerPackageStatus::RECEIVED, $admin);

                foreach ($item['sellerOrderItems'] as $sellerOrderItem) {
                    $this->sellerOrderItemStatusService->change($sellerOrderItem, SellerOrderItemStatus::RECEIVED);
                }
            }
        }
    }

    /**
     * @param array|OrderItem[] $orderItems
     *
     * @return array
     */
    private function getSellerOrderItemsGroupedBySellerAndPackageType(array $orderItems): array
    {
        $group = [];
        foreach ($orderItems as $orderItem) {
            $inventorySeller = $orderItem->getInventory()->getSeller();
            $sellerId        = $inventorySeller->getId();

            $orderShipment = $orderItem->getOrderShipment()->getTitle();
            $packageType   = $orderShipment == ShippingCategoryName::FMCG ? SellerPackageType::FMCG : SellerPackageType::NON_FMCG;

            if (!isset($group[$sellerId][$packageType])) {
                $group[$sellerId][$packageType] = [
                    'seller'           => $inventorySeller,
                    'packageType'      => $packageType,
                    'sellerOrderItems' => [],
                ];
            }

            $group[$sellerId][$packageType]['sellerOrderItems'][] = $orderItem->getSellerOrderItem();
        }

        return $group;
    }

    public function canceled(Order $order): void
    {
        parent::canceled($order);

        $order->releaseReservedStock();
    }
}
