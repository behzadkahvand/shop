<?php

namespace App\Service\OrderShipment\PartialOrderShipment;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Security\Core\Security;
use Throwable;

class PartialOrderShipmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderShipmentStatusService $orderShipmentStatusService,
        private Security $security
    ) {
    }

    public function cloneShipment(OrderShipment $orderShipment): OrderShipment
    {
        $newOrderShipment = new OrderShipment();

        $allProperties = $this->getProperties($orderShipment);
        /** @var ReflectionProperty $property */
        foreach ($allProperties as $property) {
            if (in_array($property->getName(), $this->getNotCloneableShipmentProperties())) {
                continue;
            }

            $propertyName = ucfirst($property->getName());

            $propertyValue = $orderShipment->{"get" . $propertyName}();

            if ($propertyValue instanceof ArrayCollection) {
                continue;
            }

            $newOrderShipment->{"set" . $propertyName}($propertyValue);
        }

        $newOrderShipment->setIsPrinted(false)
                         ->setPackagedCount(0)
                         ->setPodCode(random_int(10000, 99999))
                         ->setCreatedBy($this->security->getUser());

        $this->entityManager->persist($newOrderShipment);
        $this->entityManager->flush();

        return $newOrderShipment;
    }

    public function moveItems(OrderShipment $targetOrderShipment, ArrayCollection $orderItems): OrderShipment
    {
        $this->entityManager->beginTransaction();

        try {
            /** @var OrderShipment $sourceOrderShipment */
            $sourceOrderShipment = $orderItems->first()->getOrderShipment();

            foreach ($orderItems as $orderItem) {
                /** @var OrderItem $orderItem */
                $orderItem->setOrderShipment($targetOrderShipment);

                foreach ($orderItem->getDiscounts() as $discount) {
                    $discount->setOrderShipment($targetOrderShipment);
                }
            }

            $this->entityManager->flush();

            if ($sourceOrderShipment->isShipmentFullyStoraged()) {
                $this->orderShipmentStatusService->change($sourceOrderShipment, OrderShipmentStatus::WAREHOUSE);
            }

            $this->entityManager->commit();

            return $targetOrderShipment;
        } catch (Throwable $e) {
            $this->entityManager->close();
            $this->entityManager->rollback();

            throw $e;
        }
    }

    private function getProperties(OrderShipment $orderShipment): array
    {
        $reflection = new ReflectionClass($orderShipment);

        return $reflection->getProperties();
    }

    private function getNotCloneableShipmentProperties(): array
    {
        return [
            'id',
            'transaction',
            'orderItems',
            'orderShipmentStatusLogs',
            'discounts',
            'podCode',
            'isPrinted',
            'signature',
            'createdAt',
            'updatedAt',
        ];
    }
}
