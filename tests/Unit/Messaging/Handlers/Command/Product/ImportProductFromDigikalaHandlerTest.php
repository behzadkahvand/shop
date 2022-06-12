<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Seller;
use App\Exceptions\Product\Import\ProductImportException;
use App\Messaging\Handlers\Command\Product\ImportProductFromDigikalaHandler;
use App\Messaging\Messages\Command\Product\ImportProductFromDigikala;
use App\Repository\SellerRepository;
use App\Service\Product\Import\Digikala\ImportDigikalaProductService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

class ImportProductFromDigikalaHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|ImportDigikalaProductService|MockInterface|null $importer;

    private SellerRepository|LegacyMockInterface|MockInterface|null $sellerRepo;

    private Seller|LegacyMockInterface|MockInterface|null $seller;

    private ?ImportProductFromDigikalaHandler $sut;

    private LoggerInterface|LegacyMockInterface|MockInterface|null $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->importer   = Mockery::mock(ImportDigikalaProductService::class);
        $this->sellerRepo = Mockery::mock(SellerRepository::class);
        $this->seller     = Mockery::mock(Seller::class);
        $this->logger     = Mockery::mock(LoggerInterface::class);

        $this->sut = new ImportProductFromDigikalaHandler($this->importer, $this->sellerRepo, $this->logger);
    }

    public function testShouldCallImporter(): void
    {
        $dkp      = '3';
        $sellerId = 1;
        $dkSellerId = 'fake-id';
        $message  = new ImportProductFromDigikala($dkp, $sellerId, $dkSellerId);

        $this->logger->expects('debug')->with('Handling ImportProductFromDigikala message with dkp: ' . $dkp);
        $this->sellerRepo->expects('find')->with($sellerId)->andReturn($this->seller);
        $this->importer->expects('import')->with($dkp, $this->seller, $dkSellerId)->andReturnNull();

        $this->sut->__invoke($message);
    }

    public function testShouldCatchProductImportExceptionAndDoNothing(): void
    {
        $dkp      = '3';
        $sellerId = 1;
        $dkSellerId = 'fake-id';
        $message  = new ImportProductFromDigikala($dkp, $sellerId, $dkSellerId);

        $this->logger->expects('debug')->with('Handling ImportProductFromDigikala message with dkp: ' . $dkp);
        $this->sellerRepo->expects('find')->with($sellerId)->andReturn($this->seller);
        $this->importer->expects('import')->with($dkp, $this->seller, $dkSellerId)->andThrow(new ProductImportException());

        $this->sut->__invoke($message);
    }
}
