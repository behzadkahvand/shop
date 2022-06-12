<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Messaging\Handlers\Command\Product\UpdateFromOutsourceHandler;
use App\Messaging\Messages\Command\Product\UpdateFromOutsource;
use App\Repository\ProductRepository;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\SpecificationsUpdater;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;

class UpdateFromOutsourceHandlerTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $productRepo                   = Mockery::mock(ProductRepository::class);
        $em                            = Mockery::mock(EntityManagerInterface::class);
        $outsourceProductUpdateService = Mockery::mock(OutsourceProductUpdateService::class);

        $sut = new UpdateFromOutsourceHandler(
            $productRepo,
            $outsourceProductUpdateService,
            $em
        );

        $product   = new Product();
        $productId = 1;
        $message   = new UpdateFromOutsource($productId);

        $productRepo->expects('find')->with($productId)->andReturn($product);
        $outsourceProductUpdateService
            ->expects('update')
            ->with($product, Mockery::type(SpecificationsUpdater::class))
            ->andReturnNull();

        $em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();

        $sut->__invoke($message);
    }
}
