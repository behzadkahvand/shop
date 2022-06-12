<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Messaging\Handlers\Command\Product\DownloadProductImageFromDigikalaHandler;
use App\Messaging\Messages\Command\Product\DownloadProductImageFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\Update\DownloadProductImageFromDigikalaService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;

class DownloadProductImageFromDigikalaHandlerTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $product            = new Product();
        $productId          = 1;
        $url                = 'url';
        $isFeature          = true;
        $isWatermarkRemoved = true;

        $productRepo = Mockery::mock(ProductRepository::class);
        $em          = Mockery::mock(EntityManagerInterface::class);
        $downloader  = Mockery::mock(DownloadProductImageFromDigikalaService::class);

        $productRepo->expects('find')->with($productId)->andReturn($product);

        $downloader
            ->expects('download')
            ->with($product, $url, !$isWatermarkRemoved, $isFeature)
            ->andReturnNull();

        $em->expects('flush')->once()->withNoArgs()->andReturnNull();

        $message = new DownloadProductImageFromDigikala(
            $productId,
            $url,
            $isFeature,
            $isWatermarkRemoved
        );

        $sut = new DownloadProductImageFromDigikalaHandler(
            $productRepo,
            $downloader,
            $em
        );

        $sut->__invoke($message);
    }
}
