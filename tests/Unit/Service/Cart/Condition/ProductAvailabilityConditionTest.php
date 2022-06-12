<?php

namespace App\Tests\Unit\Service\Cart\Condition;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Cart\Condition\ProductAvailabilityCondition;
use App\Service\Condition\Exceptions\ProductIsNotActiveException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use App\Service\Condition\ProductAvailabilityCondition as BaseProductAvailabilityCondition;

class ProductAvailabilityConditionTest extends MockeryTestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setSellerStock(10)
            ->setLeadTime(2)
            ->setIsActive(true);

        $this->inventory->setVariant((new ProductVariant())->setProduct((new Product())));
    }

    protected function tearDown(): void
    {
        unset($this->inventory);
    }

    public function testItThrowAnExceptionWhenProductIsNotActive(): void
    {
        $this->expectException(ProductIsNotActiveException::class);

        $this->inventory->getVariant()->getProduct()->setIsActive(false);

        (new ProductAvailabilityCondition(new BaseProductAvailabilityCondition()))->apply($this->inventory, 1);
    }

    public function testItThrowAnExceptionWhenProductIsNotConfirmed(): void
    {
        $this->expectException(ProductIsNotActiveException::class);

        $this->inventory->getVariant()->getProduct()->setIsActive(true);
        $this->inventory->getVariant()->getProduct()->setStatus(ProductStatusDictionary::WAITING_FOR_ACCEPT);

        (new ProductAvailabilityCondition(new BaseProductAvailabilityCondition()))->apply($this->inventory, 1);
    }
}
