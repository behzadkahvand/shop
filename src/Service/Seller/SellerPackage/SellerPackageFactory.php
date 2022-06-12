<?php

namespace App\Service\Seller\SellerPackage;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Seller;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\Seller\SellerOrderItem\Exceptions\AllSellerOrderItemsAlreadySentException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\ValidationStrategy\SellerOrderItemValidationStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * Class SellerPackageFactory
 */
class SellerPackageFactory
{
    private EntityManagerInterface $em;

    private SellerOrderItemStatusService $sellerOrderItemStatusService;

    /**
     * SellerPackageFactory constructor.
     *
     * @param EntityManagerInterface $em
     * @param SellerOrderItemStatusService $sellerOrderItemStatusService
     */
    public function __construct(EntityManagerInterface $em, SellerOrderItemStatusService $sellerOrderItemStatusService)
    {
        $this->em = $em;
        $this->sellerOrderItemStatusService = $sellerOrderItemStatusService;
    }

    /**
     * @param array $sellerOrderItems
     * @param string $packageType
     * @param Seller $seller
     * @param SellerOrderItemValidationStrategyInterface $validationStrategy
     * @param bool $autoCreation
     *
     * @return SellerPackage
     */
    public function create(
        array $sellerOrderItems,
        string $packageType,
        Seller $seller,
        SellerOrderItemValidationStrategyInterface $validationStrategy,
        bool $autoCreation = false
    ): SellerPackage {
        $sellerOrderItems = $this->getEligibleSellerOrderItems($sellerOrderItems, $validationStrategy);
        $package          = new SellerPackage();

        $package->setType($packageType)
                ->setAutoCreation($autoCreation);

        $seller->addPackage($package);

        foreach ($sellerOrderItems as $sellerOrderItem) {
            $this->em->persist(SellerPackageItem::fromSellerOrderItem($sellerOrderItem, $package));
        }

        $this->em->persist($package);
        $this->em->beginTransaction();

        try {
            $this->em->flush();

            foreach ($sellerOrderItems as $sellerOrderItem) {
                $this->sellerOrderItemStatusService->change($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER);
            }

            $this->em->commit();
        } catch (Throwable $e) {
            $this->em->close();
            $this->em->rollback();

            throw $e;
        }

        return $package;
    }

    /**
     * @param array $sellerOrderItems
     *
     * @param SellerOrderItemValidationStrategyInterface $validationStrategy
     *
     * @return array
     */
    private function getEligibleSellerOrderItems(
        array $sellerOrderItems,
        SellerOrderItemValidationStrategyInterface $validationStrategy
    ): array {
        $validationStrategy->validate($sellerOrderItems);

        if (0 === count($sellerOrderItems)) {
            throw new AllSellerOrderItemsAlreadySentException();
        }

        return $sellerOrderItems;
    }
}
