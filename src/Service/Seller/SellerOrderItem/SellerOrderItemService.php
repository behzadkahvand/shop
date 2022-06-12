<?php

namespace App\Service\Seller\SellerOrderItem;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageType;
use App\Dictionary\ShippingCategoryName;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemCanNotBePackagedException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemIsRejectedException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class SellerOrderItemService
{
    private EntityManagerInterface $manager;

    private SellerOrderItemStatusService $sellerOrderItemStatusService;

    public function __construct(
        EntityManagerInterface $manager,
        SellerOrderItemStatusService $sellerOrderItemStatusService
    ) {
        $this->manager                      = $manager;
        $this->sellerOrderItemStatusService = $sellerOrderItemStatusService;
    }

    /**
     * @throws \App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException
     * @throws \App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemCanNotBePackagedException
     * @throws \App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException
     * @throws \Throwable
     */
    public function send(SellerOrderItem $sellerOrderItem, ?string $description = null): SellerPackage
    {
//        if (null !== $description && '' === trim($description)) {
//            throw new InvalidSellerPackageDescription();
//        }
        $orderItem = $sellerOrderItem->getOrderItem();

        if ($orderItem->getOrder()->getStatus() !== OrderStatus::CONFIRMED) {
            throw new InvalidSellerOrderStatusException();
        }

        if ($sellerOrderItem->isRejected()) {
            throw new SellerOrderItemIsRejectedException();
        }

        if (!$sellerOrderItem->isWaitingForSend()) {
            throw new SellerOrderItemCanNotBePackagedException();
        }

        if ($sellerOrderItem->isSent()) {
            return $sellerOrderItem->getPackageItem()->getPackage();
        }

        $this->manager->beginTransaction();

        try {
            $package = new SellerPackage();

            if ($description !== null) {
                $package->setDescription(trim($description));
            }

            $orderShipment = $orderItem->getOrderShipment()->getTitle();
            $package->setType($orderShipment == ShippingCategoryName::FMCG ? SellerPackageType::FMCG : SellerPackageType::NON_FMCG);

            $item = SellerPackageItem::fromSellerOrderItem($sellerOrderItem, $package);

            $sellerOrderItem->getSeller()->addPackage($package);

            $this->manager->persist($package);
            $this->manager->persist($item);

            $this->sellerOrderItemStatusService->change($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER);

            $this->manager->flush();
            $this->manager->commit();
        } catch (Throwable $e) {
            $this->manager->close();
            $this->manager->rollback();

            throw $e;
        }

        return $package;
    }
}
