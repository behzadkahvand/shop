<?php

namespace App\Service\Order\Stages;

use App\Entity\Inventory;
use App\Entity\OrderItem;
use App\Entity\SellerOrderItem;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use DateTimeInterface;

final class CreateSellerOrderItemsStage implements TagAwarePipelineStageInterface
{
    private HolidayServiceInterface $holidayService;

    /**
     * CreateSellerOrderItemsStage constructor.
     *
     * @param HolidayServiceInterface $holidayService
     */
    public function __construct(HolidayServiceInterface $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $manager = $payload->getEntityManager();

        $payload->getOrder()
                ->getOrderItems()
                ->forAll(function (int $index, OrderItem $orderItem) use ($manager) {
                    $inventory  = $orderItem->getInventory();

                    $sellerOrderItem = new SellerOrderItem();
                    $sellerOrderItem->setOrderItem($orderItem)
                                    ->setSeller($inventory->getSeller())
                                    ->setSendDate($this->getSellerSendDate($inventory));

                    $orderItem->setSellerOrderItem($sellerOrderItem);

                    $manager->persist($sellerOrderItem);

                    return true;
                });

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 88;
    }

    /**
     * @param Inventory $inventory
     *
     * @return DateTimeInterface
     */
    private function getSellerSendDate(Inventory $inventory): DateTimeInterface
    {
        $leadTime     = $inventory->getLeadTime();
        $sellerSentDate = new \DateTimeImmutable('today');
        $seller         = $inventory->getSeller();

        while (0 < $leadTime) {
            $sellerSentDate = $sellerSentDate->modify('1 day');

            if (
                $this->holidayService->isOpenForSupply($sellerSentDate) &&
                $this->holidayService->isOpenForSupply($sellerSentDate, $seller)
            ) {
                $leadTime--;
            }
        }

        return $sellerSentDate;
    }
}
