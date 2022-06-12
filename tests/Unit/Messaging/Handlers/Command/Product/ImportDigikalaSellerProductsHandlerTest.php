<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Messaging\Handlers\Command\Product\ImportDigikalaSellerProductsHandler;
use App\Messaging\Messages\Command\Product\ImportDigikalaSellerProducts;
use App\Service\Product\Import\Digikala\ImportDigikalaSellerProductsService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ImportDigikalaSellerProductsHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|ImportDigikalaSellerProductsService|MockInterface|null $importer;

    private ImportDigikalaSellerProductsHandler|null $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->importer = Mockery::mock(ImportDigikalaSellerProductsService::class);

        $this->sut = new ImportDigikalaSellerProductsHandler($this->importer);
    }

    public function testShouldCallImporter(): void
    {
        $digikalaSellerId = 'x1';
        $sellerId         = 1;
        $message          = new ImportDigikalaSellerProducts($digikalaSellerId, $sellerId);

        $this->importer->expects('import')->with($sellerId, $digikalaSellerId)->andReturnNull();

        $this->sut->__invoke($message);
    }
}
