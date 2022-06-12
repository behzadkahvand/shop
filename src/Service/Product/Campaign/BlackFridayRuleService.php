<?php

namespace App\Service\Product\Campaign;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Exceptions\Product\Campaign\InvalidCampaignRequestException;
use App\Messaging\Handlers\Command\Product\AddBuyBoxToProductHandler;
use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use App\Repository\InventoryRepository;
use App\Service\Product\BuyBox\BuyBoxValidatorService;
use Doctrine\ORM\EntityManagerInterface;

class BlackFridayRuleService
{
    public function __construct(
        protected InventoryRepository $inventoryRepository,
        protected BuyBoxValidatorService $buyBoxValidator,
        protected AddBuyBoxToProductHandler $addBuyBoxToProductHandler,
        protected EntityManagerInterface $manager
    ) {
    }

    /**
     * @throws InvalidCampaignRequestException
     */
    public function apply(Inventory $inventory, BlackFridayRequest $request): void
    {
        if (!$inventory->isConfirmed() || !$inventory->getIsActive()) {
            throw new InvalidCampaignRequestException('inventory is not active or confirmed');
        }

        $product = $inventory->getVariant()->getProduct();

        if (0 === $request->getStock()) {
            $inventory->setSellerStock($request->getStock());
            $inventory->setHasCampaign(false);

            $this->manager->flush();

            $this->reviseBuyBox($product);
            return;
        }

        if ($inventory->getHasCampaign() === true) {
            $inventory->setSellerStock($request->getStock());

            $this->manager->flush();

            return;
        }

        $seller            = $inventory->getSeller();
        $campaignInventory = $this->inventoryRepository->findOneCampaignInventoryByProduct($product);
        if (isset($campaignInventory) && !$campaignInventory->isBelongTo($seller)) {
            throw new InvalidCampaignRequestException('Another seller has campaign on this product');
        }

        $this->validateFinalPrice($request, $inventory);

        // If this is the first inventory of this product that is going into campaign
        // then it should pass buyBox conditions
        if (!isset($campaignInventory)) {
            $oldFinalPrice = $inventory->getFinalPrice();
            $inventory->setFinalPrice($request->getFinalPrice());
            if (!$this->meetsBuyBoxCondition($inventory, $product)) {
                $inventory->setFinalPrice($oldFinalPrice);
                throw new InvalidCampaignRequestException('Buy box condition is not met');
            }
        }

        $inventory->setFinalPrice($request->getFinalPrice());
        $inventory->setHasCampaign(true);
        $inventory->setSellerStock($request->getStock());

        $this->manager->flush();

        $this->reviseBuyBox($product);
    }

    private function meetsBuyBoxCondition(Inventory $inventory, Product $product): bool
    {
        return
            $inventory->isBuyBox() ||
            $this->buyBoxValidator->validate($product, $inventory);
    }

    private function reviseBuyBox(Product $product): void
    {
        $this->addBuyBoxToProductHandler->__invoke(new AddBuyBoxToProduct($product->getId()));
    }

    /**
     * @throws InvalidCampaignRequestException
     */
    private function validateFinalPrice(BlackFridayRequest $request, Inventory $inventory)
    {
        if ($request->getFinalPrice() >= $inventory->getPrice()) {
            throw new InvalidCampaignRequestException(
                'Final price is not smaller than initial price, initPrice: ' . $inventory->getPrice()
            );
        }
    }
}
