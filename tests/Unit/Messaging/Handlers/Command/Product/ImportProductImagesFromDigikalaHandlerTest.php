<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Messaging\Handlers\Command\Product\ImportProductImagesFromDigikalaHandler;
use App\Messaging\Messages\Command\Product\ImportProductImagesFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\ImageUpdater;
use App\Service\Product\Update\PropertyUpdaters\PropertyUpdaterFactory;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class ImportProductImagesFromDigikalaHandlerTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $productRepo = Mockery::mock(ProductRepository::class);
        $outsourceProductUpdateService = Mockery::mock(OutsourceProductUpdateService::class);
        $updaterFactory = Mockery::mock(PropertyUpdaterFactory::class);

        $productId = 1;
        $product = new Product();

        $productRepo->expects('find')->with($productId)->andReturn($product);

        $outsourceProductUpdateService
            ->expects('update')
            ->with($product, Mockery::type(ImageUpdater::class))
            ->andReturnNull();

        $updaterFactory->expects('makeImageUpdater')->withNoArgs()->andReturn(Mockery::mock(ImageUpdater::class));

        $message = new ImportProductImagesFromDigikala($productId);

        $sut = new ImportProductImagesFromDigikalaHandler(
            $productRepo,
            $outsourceProductUpdateService,
            $updaterFactory
        );

        $sut->__invoke($message);
    }
}
