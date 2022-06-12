<?php

namespace App\Tests\Unit\Service\Product\Update;

use App\Entity\Product;
use App\Exceptions\Product\Import\ProductImportException;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdater;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OutsourceProductUpdateServiceTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $dkp = 123;
        $digikalaPageData = ['data' => ['key' => 'value']];
        $client = Mockery::mock(HttpClientInterface::class);
        $updater_1 = Mockery::mock(PropertyUpdater::class);
        $updater_2 = Mockery::mock(PropertyUpdater::class);

        $product = new Product();
        $product->setDigikalaDkp($dkp);

        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('getContent')->withNoArgs()->andReturn(json_encode($digikalaPageData));
        $client
            ->expects('request')
            ->with('GET', 'https://api.digikala.com/v1/product/' . $dkp . '/')
            ->andReturn($response);

        $updater_1->expects('update')->with($product, ['key' => 'value'])->andReturnNull();
        $updater_2->expects('update')->with($product, ['key' => 'value'])->andReturnNull();

        $sut = new OutsourceProductUpdateService($client);

        $sut->update($product, $updater_1, $updater_2);
    }

    public function testShouldThrowExceptionIfProductIsInactiveInDigikala(): void
    {
        $dkp = 123;
        $digikalaPageData = ['data' => ['product' => ['is_inactive' => true]]];

        $client = Mockery::mock(HttpClientInterface::class);
        $updater_1 = Mockery::mock(PropertyUpdater::class);

        $product = new Product();
        $product->setDigikalaDkp($dkp);

        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('getContent')->withNoArgs()->andReturn(json_encode($digikalaPageData));

        $client
            ->expects('request')
            ->with('GET', 'https://api.digikala.com/v1/product/' . $dkp . '/')
            ->andReturn($response);


        $sut = new OutsourceProductUpdateService($client);

        $this->expectException(ProductImportException::class);
        $this->expectErrorMessage('Product is inactive in digikala');

        $sut->update($product, $updater_1);
    }
}
