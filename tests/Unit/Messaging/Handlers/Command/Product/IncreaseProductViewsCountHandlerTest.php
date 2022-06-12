<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Messaging\Handlers\Command\Product\IncreaseProductViewsCountHandler;
use App\Messaging\Messages\Command\Product\IncreaseProductViewsCount;
use App\Repository\ProductRepository;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;

class IncreaseProductViewsCountHandlerTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $productRepo = Mockery::mock(ProductRepository::class);
        $em          = Mockery::mock(EntityManagerInterface::class);

        $sut = new IncreaseProductViewsCountHandler(
            $em,
            $productRepo
        );

        $product   = new Product();
        $productId = 1;
        self::assertEquals(0, $product->getVisits());

        $productRepo->expects('find')->with($productId)->andReturn($product);

        $em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();

        $message = new IncreaseProductViewsCount($productId);

        $sut->__invoke($message);

        self::assertEquals(1, $product->getVisits());
    }
}
