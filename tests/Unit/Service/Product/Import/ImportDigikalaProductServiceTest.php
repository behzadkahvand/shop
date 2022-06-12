<?php

namespace App\Tests\Unit\Service\Product\Import;

use App\Document\ProductImportLog;
use App\Entity\Product;
use App\Entity\Seller;
use App\Exceptions\Product\Import\DuplicateProductException;
use App\Messaging\Messages\Command\Product\ImportProductImagesFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\builder\ProductBuilder;
use App\Service\Product\Import\Digikala\ImportDigikalaProductService;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\BrandUpdater;
use App\Service\Product\Update\PropertyUpdaters\CategoryUpdater;
use App\Service\Product\Update\PropertyUpdaters\PropertyUpdaterFactory;
use App\Service\Product\Update\PropertyUpdaters\SpecificationsUpdater;
use App\Service\Product\Update\PropertyUpdaters\TitleUpdater;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportDigikalaProductServiceTest extends BaseUnitTestCase
{
    private ProductBuilder|LegacyMockInterface|MockInterface|null $productBuilder;

    private LegacyMockInterface|MockInterface|OutsourceProductUpdateService|null $outsourceProductUpdateService;

    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    private LegacyMockInterface|ProductRepository|MockInterface|null $productRepo;

    private LegacyMockInterface|MockInterface|PropertyUpdaterFactory|null $updaterFactory;

    private LegacyMockInterface|MessageBusInterface|MockInterface|null $bus;

    private LegacyMockInterface|Product|MockInterface|null $product;

    private Seller|LegacyMockInterface|MockInterface|null $seller;

    private ImportDigikalaProductService|null $sut;
    private DocumentManager|LegacyMockInterface|MockInterface|null $documentManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productBuilder                = Mockery::mock(ProductBuilder::class);
        $this->outsourceProductUpdateService = Mockery::mock(OutsourceProductUpdateService::class);
        $this->em                            = Mockery::mock(EntityManagerInterface::class);
        $this->productRepo                   = Mockery::mock(ProductRepository::class);
        $this->updaterFactory                = Mockery::mock(PropertyUpdaterFactory::class);
        $this->bus                           = Mockery::mock(MessageBusInterface::class);
        $this->product                       = Mockery::mock(Product::class);
        $this->seller                        = Mockery::mock(Seller::class);
        $this->documentManager               = Mockery::mock(DocumentManager::class);

        $this->sut = new ImportDigikalaProductService(
            $this->productBuilder,
            $this->outsourceProductUpdateService,
            $this->productRepo,
            $this->em,
            $this->bus,
            $this->updaterFactory,
            $this->documentManager
        );
    }

    public function testShouldWorkCorrectly(): void
    {
        $dkp   = 12;
        $title = 'dummy title';

        $this->documentManager->expects('persist')->with(ProductImportLog::class)->andReturnNull();
        $this->documentManager->expects('flush')->twice()->withNoArgs()->andReturnNull();

        $this->productBuilder->expects('withDigikalaDkp')->with($dkp)->andReturnSelf();
        $this->productBuilder->expects('withSeller')->with($this->seller)->andReturnSelf();
        $this->productBuilder->expects('build')->withNoArgs()->andReturn($this->product);

        $this->updaterFactory->expects('makeTitleUpdater')->withNoArgs()->andReturn(Mockery::mock(TitleUpdater::class));
        $this->updaterFactory->expects('makeBrandUpdater')->withNoArgs()->andReturn(Mockery::mock(BrandUpdater::class));
        $this->updaterFactory->expects('makeCategoryUpdater')->withNoArgs()->andReturn(Mockery::mock(CategoryUpdater::class));
        $this->updaterFactory->expects('makeSpecificationsUpdater')->withNoArgs()->andReturn(Mockery::mock(SpecificationsUpdater::class));

        $this->outsourceProductUpdateService
            ->expects('update')
            ->with(
                $this->product,
                Mockery::type(TitleUpdater::class),
                Mockery::type(BrandUpdater::class),
                Mockery::type(CategoryUpdater::class),
                Mockery::type(SpecificationsUpdater::class)
            )
            ->andReturnNull();

        $this->product->expects('getTitle')->withNoArgs()->andReturn($title);
        $this->product->expects('getDigikalaDkp')->withNoArgs()->andReturn($dkp);
        $this->productRepo->expects('findByTitleOrDigikalaDkp')->with($title, $dkp)->andReturnNull();

        $this->em->expects('persist')->with($this->product)->andReturnNull();
        $this->em->expects('flush')->with()->andReturnNull();

        $this->product->expects('getId')->withNoArgs()->andReturn(1);
        $this->bus->expects('dispatch')->with(Mockery::type(ImportProductImagesFromDigikala::class));

        $this->seller->expects('getId')->withNoArgs()->andReturn(1);

        $this->sut->import($dkp, $this->seller, 'fake-id');
    }

    public function testShouldThrowExceptionIfProductAlreadyExist(): void
    {
        $dkp   = 12;
        $title = 'dummy title';

        $this->documentManager->expects('persist')->with(ProductImportLog::class)->andReturnNull();
        $this->documentManager->expects('flush')->twice()->withNoArgs()->andReturnNull();

        $this->productBuilder->expects('withDigikalaDkp')->with($dkp)->andReturnSelf();
        $this->productBuilder->expects('withSeller')->with($this->seller)->andReturnSelf();
        $this->productBuilder->expects('build')->withNoArgs()->andReturn($this->product);

        $this->updaterFactory->expects('makeTitleUpdater')->withNoArgs()->andReturn(Mockery::mock(TitleUpdater::class));
        $this->updaterFactory->expects('makeBrandUpdater')->withNoArgs()->andReturn(Mockery::mock(BrandUpdater::class));
        $this->updaterFactory->expects('makeCategoryUpdater')->withNoArgs()->andReturn(Mockery::mock(CategoryUpdater::class));
        $this->updaterFactory->expects('makeSpecificationsUpdater')->withNoArgs()->andReturn(Mockery::mock(SpecificationsUpdater::class));

        $this->seller->expects('getId')->withNoArgs()->andReturn(1);

        $this->outsourceProductUpdateService
            ->expects('update')
            ->with(
                $this->product,
                Mockery::type(TitleUpdater::class),
                Mockery::type(BrandUpdater::class),
                Mockery::type(CategoryUpdater::class),
                Mockery::type(SpecificationsUpdater::class)
            )
            ->andReturnNull();

        $this->product->expects('getTitle')->withNoArgs()->andReturn($title);
        $this->product->expects('getDigikalaDkp')->withNoArgs()->andReturn($dkp);
        $this->productRepo->expects('findByTitleOrDigikalaDkp')->with($title, $dkp)->andReturn(Mockery::mock(Product::class));

        $this->expectException(DuplicateProductException::class);

        $this->sut->import($dkp, $this->seller, 'fake-id');
    }
}
