<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Entity\ProductOptionValue;
use App\Messaging\Handlers\Command\Product\AddColorsToProductHandler;
use App\Messaging\Messages\Command\Product\AddColorsToProduct;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Psr\Log\LoggerInterface;

class AddColorsToProductHandlerTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerInterfaceMock;

    private Mockery\LegacyMockInterface|Product|Mockery\MockInterface|null $productMock;

    private ?AddColorsToProductHandler $addColorsToProductHandler;

    private ?int $productId;

    private LoggerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface|null $loggerMock;

    private ProductOptionValue|Mockery\LegacyMockInterface|Mockery\MockInterface|null $productOptionValueMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerInterfaceMock = Mockery::mock(EntityManagerInterface::class);
        $this->loggerMock                 = Mockery::mock(LoggerInterface::class);
        $this->productMock                = Mockery::mock(Product::class);
        $this->productOptionValueMock     = Mockery::mock(ProductOptionValue::class);

        $this->addColorsToProductHandler = new AddColorsToProductHandler(
            $this->entityManagerInterfaceMock
        );

        $this->productId = 12;
    }

    public function testItDoNothingWhenProductNotFound(): void
    {
        $addBuyBoxToProduct = new AddColorsToProduct($this->productId);

        $this->entityManagerInterfaceMock->expects('getReference')
                                         ->andReturnNull();

        $this->addColorsToProductHandler->setLogger($this->loggerMock);

        $this->loggerMock->expects('error')
                         ->with(sprintf('It can not add colors to product %d when product not exist!', $this->productId))
                         ->andReturn();

        $this->addColorsToProductHandler->__invoke($addBuyBoxToProduct);
    }

    public function testItDoNothingWhenColorsNotExists(): void
    {
        $addTitleAndMetaDescription = new AddColorsToProduct($this->productId);

        $this->entityManagerInterfaceMock->expects('getReference')
                                         ->andReturn($this->productMock);

        $this->productMock->expects('getColorsOption')
                          ->andReturn(new ArrayCollection([]));

        $this->addColorsToProductHandler->__invoke($addTitleAndMetaDescription);
    }

    public function testItCanAddColors(): void
    {
        $addTitleAndMetaDescription = new AddColorsToProduct($this->productId);

        $this->entityManagerInterfaceMock->expects('getReference')
                                         ->andReturn($this->productMock);

        $this->productMock->expects('getColorsOption')
                          ->andReturn(new ArrayCollection([$this->productOptionValueMock]));

        $this->productOptionValueMock->expects('getCode')->andReturn('white');
        $this->productOptionValueMock->expects('getValue')->andReturn('سفید');
        $this->productOptionValueMock->expects('getAttributes')->andReturn(["hex" => "#ffffff"]);

        $this->productMock->expects('setColors')->andReturnSelf();

        $this->entityManagerInterfaceMock->expects('flush');

        $this->addColorsToProductHandler->__invoke($addTitleAndMetaDescription);
    }
}
