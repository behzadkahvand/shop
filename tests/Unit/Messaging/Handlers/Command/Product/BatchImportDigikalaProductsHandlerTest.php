<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Messaging\Handlers\Command\Product\BatchImportDigikalaProductsHandler;
use App\Messaging\Messages\Command\Product\BatchImportDigikalaProducts;
use App\Service\Product\Import\Digikala\BatchImportDigikalaProductsService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class BatchImportDigikalaProductsHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|BatchImportDigikalaProductsService|MockInterface|null $importer;

    private BatchImportDigikalaProductsHandler|null $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->importer = Mockery::mock(BatchImportDigikalaProductsService::class);

        $this->sut = new BatchImportDigikalaProductsHandler($this->importer);
    }

    public function testShouldCallImporter(): void
    {
        $url      = 'https://digikala.com/seller/fake-seller';
        $sellerId = 1;
        $dkSellerId = 'fake-id';
        $message  = new BatchImportDigikalaProducts($url, $sellerId, $dkSellerId);

        $this->importer->expects('import')->with($url, $sellerId, $dkSellerId)->andReturnNull();

        $this->sut->__invoke($message);
    }
}
