<?php

namespace App\Tests\Integration\TestDoubles\Campaign;

use App\Entity\Inventory;
use App\Exceptions\Product\Campaign\InvalidCampaignRequestException;
use App\Service\Product\Campaign\BlackFridayRequest;
use App\Service\Product\Campaign\BlackFridayRuleService;

class FakeBlackFridayRuleService extends BlackFridayRuleService
{
    protected array $success;

    protected array $fail;

    public function __construct(protected string $errorMessage)
    {
    }

    public function apply(
        Inventory $inventory,
        BlackFridayRequest $request
    ): void {
        if (in_array($inventory->getId(), $this->success)) {
            $inventory->setFinalPrice($request->getFinalPrice());
            $inventory->setSellerStock($request->getStock());
            $inventory->setHasCampaign(true);
        } elseif (in_array($inventory->getId(), $this->fail)) {
            throw new InvalidCampaignRequestException($this->errorMessage);
        }
    }

    public function shouldSuccess(...$inventoryIds): void
    {
        $this->success = $inventoryIds;
    }

    public function shouldFail(...$inventoryIds): void
    {
        $this->fail = $inventoryIds;
    }
}
