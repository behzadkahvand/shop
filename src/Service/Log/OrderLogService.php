<?php

namespace App\Service\Log;

use App\Entity\Admin;
use App\Entity\AdminUserSellerOrderItemStatusLog;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipmentStatusLog;
use App\Entity\OrderStatusLog;
use App\Entity\SellerOrderItemStatusLog;
use App\Entity\SellerUserSellerOrderItemStatusLog;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;

class OrderLogService
{
    protected const ORDER_ITEM_LOG_ACTION_CHANGED_QUANTITY = "quantityLogs";
    protected const ORDER_ITEM_LOG_ACTION_DELETED_ITEM = "deletedLogs";

    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
    ) {
    }

    public function onOrderItemSetDeletedBy(int $orderItemId, ?Admin $admin = null): void
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->find($orderItemId);
        $orderItem->setDeletedBy($admin);
    }

    public function getOrderLogsTracking(int $identifier): array
    {
        $order = $this->orderRepository->findWithTrackingLogs($identifier);

        if (!$order) {
            throw new \Exception(sprintf("Order with identifier %d not found!", $identifier));
        }

        $orderStatusLogs = $this->getOrderStatusLogs($order);

        $orderShipmentStatusLogs = $this->getOrderShipmentLogs($order);

        $orderItemsLogs = $this->getOrderItemLogs($order);

        $sellerOrderItemStatusLogs = $this->getSellerOrderItemStatusLogs($order);

        return compact("orderStatusLogs", "orderShipmentStatusLogs", "orderItemsLogs", "sellerOrderItemStatusLogs");
    }

    protected function getOrderStatusLogs(Order $order): array
    {
        $logs = $order->getOrderStatusLogs();
        if (!$logs) {
            return [];
        }

        $orderStatusLogs = [];
        /** @var OrderStatusLog $log */
        foreach ($logs as $log) {
            $orderStatusLogs[] = [
                'statusFrom' => $log->getStatusFrom(),
                'statusTo' => $log->getStatusTo(),
                'updatedAt' => $log->getCreatedAt()->format("Y-m-d H:i:s"),
                'updatedBy' => $log->getUser() ? $log->getUser()->getEmail() : ""
            ];
        }

        return $orderStatusLogs;
    }

    protected function getOrderShipmentLogs(Order $order): array
    {
        $shipments = $order->getShipments();
        if (!$shipments) {
            return [];
        }


        $orderShipmentStatusLogs = [];
        foreach ($shipments as $shipment) {
            $shipmentLogs = $shipment->getOrderShipmentStatusLogs();
            if (!$shipmentLogs) {
                continue;
            }

            /** @var OrderShipmentStatusLog $log */
            foreach ($shipmentLogs as $log) {
                $orderShipmentStatusLogs[] = [
                    "orderShipmentId" => $shipment->getId(),
                    "orderShipmentTitle" => $shipment->getTitle(),
                    'statusFrom' => $log->getStatusFrom(),
                    'statusTo' => $log->getStatusTo(),
                    'updatedAt' => $log->getCreatedAt()->format("Y-m-d H:i:s"),
                    'updatedBy' => $log->getUser() ? $log->getUser()->getEmail() : ""
                ];
            }
        }

        return $orderShipmentStatusLogs;
    }

    protected function getOrderItemLogs(Order $order): array
    {
        $orderItems = $order->getOrderItems();
        if (!$orderItems) {
            return [];
        }

        $orderItemLogs = [
            self::ORDER_ITEM_LOG_ACTION_CHANGED_QUANTITY => [],
            self::ORDER_ITEM_LOG_ACTION_DELETED_ITEM => []
        ];
        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $quantityLogs = $this->handleOrderItemQuantityLogs($orderItem);
            if ($quantityLogs) {
                $orderItemLogs[self::ORDER_ITEM_LOG_ACTION_CHANGED_QUANTITY][] = call_user_func_array('array_merge', $quantityLogs);
            }

            $deletedLogs = $this->handleOrderItemDeletedLogs($orderItem);
            if ($deletedLogs) {
                $orderItemLogs[self::ORDER_ITEM_LOG_ACTION_DELETED_ITEM][] = call_user_func_array('array_merge', $deletedLogs);
            }
        }

        return $orderItemLogs;
    }

    protected function getSellerOrderItemStatusLogs(Order $order): array
    {
        $orderItems = $order->getOrderItems();
        if (!$orderItems) {
            return [];
        }

        $sellerOrderItemLogs = [];
        foreach ($orderItems as $orderItem) {
            $sellerOrderItem = $orderItem->getSellerOrderItem();
            if (!$sellerOrderItem || !($sellerItemLogs = $sellerOrderItem->getSellerOrderItemStatusLog())) {
                continue;
            }

            /** @var SellerOrderItemStatusLog $sellerItemLog */
            foreach ($sellerItemLogs as $sellerItemLog) {
                $result = [
                    "orderItemId"  => $orderItem->getId(),
                    "inventoryId"  => $orderItem->getInventory()->getId(),
                    "productId"    => $orderItem->getInventory()->getVariant()->getProduct()->getId(),
                    "productTitle" => $orderItem->getInventory()->getVariant()->getProduct()->getTitle(),
                    'statusFrom'   => $sellerItemLog->getStatusFrom(),
                    'statusTo'     => $sellerItemLog->getStatusTo(),
                    'updatedAt'    => $sellerItemLog->getCreatedAt()->format("Y-m-d H:i:s"),
                    'updatedBy'    => ""
                ];

                $result = $this->checkSellerLogUpdateBy($sellerItemLog, $result);

                $sellerOrderItemLogs[] = $result;
            }
        }

        return $sellerOrderItemLogs;
    }

    private function handleOrderItemQuantityLogs(OrderItem $orderItem): array
    {
        $orderItemLogs = [];
        $quantityLogs = $orderItem->getLogs();

        if ($quantityLogs) {
            foreach ($quantityLogs as $quantityLog) {
                $orderItemLogs[] = [
                    "orderItemId"  => $orderItem->getId(),
                    "inventoryId"  => $orderItem->getInventory()->getId(),
                    "productId"    => $orderItem->getInventory()->getVariant()->getProduct()->getId(),
                    "productTitle" => $orderItem->getInventory()->getVariant()->getProduct()->getTitle(),
                    "quantityFrom" => $quantityLog->getQuantityFrom(),
                    "quantityTo"   => $quantityLog->getQuantityTo(),
                    'updatedAt'    => $quantityLog->getCreatedAt()->format("Y-m-d H:i:s"),
                    'updatedBy'    => $quantityLog->getUser() ? $quantityLog->getUser()->getEmail() : ""
                ];
            }
        }

        return $orderItemLogs;
    }

    private function handleOrderItemDeletedLogs(OrderItem $orderItem): array
    {
        $orderItemLogs = [];

        if ($orderItem->getDeletedAt()) {
            $orderItemLogs[] = [
                "orderItemId"  => $orderItem->getId(),
                "inventoryId"  => $orderItem->getInventory()->getId(),
                "productId"    => $orderItem->getInventory()->getVariant()->getProduct()->getId(),
                "productTitle" => $orderItem->getInventory()->getVariant()->getProduct()->getTitle(),
                'deletedAt'    => $orderItem->getDeletedAt()->format("Y-m-d H:i:s"),
                'deletedBy'    => $orderItem?->getDeletedBy()?->getId()
            ];
        }

        return $orderItemLogs;
    }

    private function checkSellerLogUpdateBy(SellerOrderItemStatusLog $sellerItemLog, array $result): array
    {
        if ($sellerItemLog instanceof SellerUserSellerOrderItemStatusLog) {
            $result['updatedBy'] = $sellerItemLog->getUser()->getUsername();
        } elseif ($sellerItemLog instanceof AdminUserSellerOrderItemStatusLog) {
            $result['updatedBy'] = $sellerItemLog->getUser()->getEmail();
        }

        return $result;
    }
}
