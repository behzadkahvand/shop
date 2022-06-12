<?php

namespace App\Tests\Unit\Service\ProductVariant;

use App\Dictionary\InventoryStatus;
use App\DTO\Admin\ProductVariantAndInventoryData;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Repository\InventoryRepository;
use App\Repository\ProductVariantRepository;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Service\Inventory\Validation\InventoryPriceValidator;
use App\Service\ProductVariant\CreateProductVariantWithInventoryService;
use App\Service\ProductVariant\Exceptions\InvalidOptionValuesException;
use App\Service\ProductVariant\Exceptions\InvalidLeadTimeException;
use App\Service\ProductVariant\Exceptions\InventoryExistenceException;
use App\Service\ProductVariant\Exceptions\ProductIdentifierException;
use App\Service\ProductVariant\Exceptions\ProductOptionsNotSetException;
use App\Service\ProductVariant\ProductVariantFactory;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class CreateProductVariantWithInventoryServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected LegacyMockInterface|ProductVariantRepository|MockInterface|null $productVariantRepositoryMock;

    protected InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepositoryMock;

    protected ProductVariantFactory|LegacyMockInterface|MockInterface|null $factoryMock;

    protected ProductVariantAndInventoryData|LegacyMockInterface|MockInterface|null $productVariantAndInventoryDTOMock;

    protected LegacyMockInterface|MockInterface|ProductVariant|null $productVariantMock;

    protected LegacyMockInterface|Product|MockInterface|null $productMock;

    protected LegacyMockInterface|ProductOption|MockInterface|null $productOptionMock;

    protected ProductOptionValue|LegacyMockInterface|MockInterface|null $productOptionValueMock;

    protected Seller|LegacyMockInterface|MockInterface|null $sellerMock;

    protected Inventory|LegacyMockInterface|MockInterface|null $inventoryMock;

    protected LegacyMockInterface|MockInterface|MaxInventoryDiscountValidator|null $discountValidator;

    protected ?CreateProductVariantWithInventoryService $createProductVariantWithInventory;

    private LegacyMockInterface|MockInterface|InventoryPriceValidator|null $inventoryPriceValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);

        $this->productVariantRepositoryMock = Mockery::mock(ProductVariantRepository::class);

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepository::class);

        $this->factoryMock = Mockery::mock(ProductVariantFactory::class);

        $this->productMock = Mockery::mock(Product::class);
        $this->productMock->shouldReceive(['productIdentifierConstraintIsResolved' => true]);

        $this->productVariantAndInventoryDTOMock = Mockery::mock(ProductVariantAndInventoryData::class);

        $this->productVariantMock = Mockery::mock(ProductVariant::class);

        $this->productOptionMock = Mockery::mock(ProductOption::class);

        $this->productOptionValueMock = Mockery::mock(ProductOptionValue::class);

        $this->sellerMock = Mockery::mock(Seller::class);

        $this->inventoryMock = Mockery::mock(Inventory::class);

        $this->discountValidator = Mockery::mock(MaxInventoryDiscountValidator::class);

        $this->inventoryPriceValidator = Mockery::mock(InventoryPriceValidator::class);

        $this->createProductVariantWithInventory = new CreateProductVariantWithInventoryService(
            $this->productVariantRepositoryMock,
            $this->inventoryRepositoryMock,
            $this->factoryMock,
            $this->em,
            $this->discountValidator,
            $this->inventoryPriceValidator
        );

        $this->createProductVariantWithInventory->setCheckInitialStatus(false);
    }

    public function testItThrowsExceptionWhenProductIdentifierConstraintIsNotResolved(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $productMock = Mockery::mock(Product::class);
        $productMock->shouldReceive(['productIdentifierConstraintIsResolved' => false, 'getId' => 1])
                    ->once()
                    ->withNoArgs();

        $productVariantAndInventoryDTOMock = Mockery::mock(ProductVariantAndInventoryData::class);
        $productVariantAndInventoryDTOMock->shouldReceive(['getProduct' => $productMock])->once()->withNoArgs();

        $this->expectException(ProductIdentifierException::class);
        $this->expectExceptionMessage(
            'Product with id 1 require at least 1 product identifier. Creating inventory not allowed!'
        );

        $this->createProductVariantWithInventory->perform($productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenOptionValueshasASameOption(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);

        $this->productOptionMock->shouldReceive('getId')
                                ->twice()
                                ->withNoArgs()
                                ->andReturn(1, 1);

        $this->expectException(InvalidOptionValuesException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Selected option values is invalid!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenOptionValuesIsNotMatchToProduct(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(1, 3, 1, 4);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->expectException(ProductOptionsNotSetException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product options not set!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenAllProductOptionNotSet(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(5)
                                ->withNoArgs()
                                ->andReturn(1, 3, 1, 3, 4);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->expectException(ProductOptionsNotSetException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product options not set!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenAdditionalOptionValuesSet(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->times(3)
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(5)
                                ->withNoArgs()
                                ->andReturn(1, 3, 4, 1, 4);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->expectException(ProductOptionsNotSetException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product options not set!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenProductInventoryExists(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(1, 3, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturn($this->inventoryMock);

        $this->expectException(InventoryExistenceException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Inventory Exists!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenLeadTimeIsGreaterThanCategoryMaxLead(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(4);

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(1, 3, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturnNull();

        $this->expectException(InvalidLeadTimeException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Lead time has invalid value!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItThrowsExceptionWhenLeadTimeIsNegative(): void
    {
        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(-4);

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(1, 3, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturnNull();

        $this->expectException(InvalidLeadTimeException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Lead time has invalid value!');

        $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);
    }

    public function testItCanCreateProductVariantAndInventory(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->productVariantMock)
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->inventoryMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->twice()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(2);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn('code');
        $this->productVariantAndInventoryDTOMock->shouldReceive('getPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getFinalPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(45000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('isActive')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnTrue();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getMaxPurchasePerOrder')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(6);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getStock')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(100);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSellerCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn('sellerCode');

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(3, 1, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);
        $this->productVariantMock->shouldReceive('setProduct')
                                 ->once()
                                 ->with($this->productMock)
                                 ->andReturn($this->productVariantMock);
        $this->productVariantMock->shouldReceive('setCode')
                                 ->once()
                                 ->with('code')
                                 ->andReturn($this->productVariantMock);
        $this->productVariantMock->shouldReceive('addOptionValue')
                                 ->twice()
                                 ->with($this->productOptionValueMock)
                                 ->andReturn($this->productVariantMock);
        $this->productVariantMock->shouldReceive('addInventory')
                                 ->once()
                                 ->with($this->inventoryMock)
                                 ->andReturnSelf();

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturnNull();
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->factoryMock->shouldReceive('getProductVariant')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->productVariantMock);
        $this->factoryMock->shouldReceive('getInventory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('setSeller')
                            ->once()
                            ->with($this->sellerMock)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setFinalPrice')
                            ->once()
                            ->with(45000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setIsActive')
                            ->once()
                            ->with(true)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setMaxPurchasePerOrder')
                            ->once()
                            ->with(6)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerStock')
                            ->once()
                            ->with(100)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setLeadTime')
                            ->once()
                            ->with(2)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::CONFIRMED)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerCode')
                            ->once()
                            ->with('sellerCode')
                            ->andReturn($this->inventoryMock);

        $this->inventoryPriceValidator->expects('validate')->with($this->inventoryMock)->andReturnNull();

        $this->discountValidator
            ->shouldReceive('validate')
            ->once()
            ->with(10)
        ->andReturnTrue();

        $result = $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);

        self::assertEquals($this->productVariantMock, $result);
    }

    public function testItCanCreateProductVariantWithoutCodeAndInventory(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->productVariantMock)
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->inventoryMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->twice()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(2);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnNull();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getFinalPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(45000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('isActive')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnTrue();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getMaxPurchasePerOrder')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(6);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getStock')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(100);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSellerCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnNull();

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(3, 1, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);
        $this->productVariantMock->shouldReceive('setProduct')
                                 ->once()
                                 ->with($this->productMock)
                                 ->andReturn($this->productVariantMock);
        $this->productVariantMock->shouldReceive('addOptionValue')
                                 ->twice()
                                 ->with($this->productOptionValueMock)
                                 ->andReturn($this->productVariantMock);
        $this->productVariantMock->shouldReceive('addInventory')
                                 ->once()
                                 ->with($this->inventoryMock)
                                 ->andReturnSelf();

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturnNull();
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->factoryMock->shouldReceive('getProductVariant')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->productVariantMock);
        $this->factoryMock->shouldReceive('getInventory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('setSeller')
                            ->once()
                            ->with($this->sellerMock)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setFinalPrice')
                            ->once()
                            ->with(45000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setIsActive')
                            ->once()
                            ->with(true)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setMaxPurchasePerOrder')
                            ->once()
                            ->with(6)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerStock')
                            ->once()
                            ->with(100)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setLeadTime')
                            ->once()
                            ->with(2)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::CONFIRMED)
                            ->andReturn($this->inventoryMock);

        $this->inventoryPriceValidator->expects('validate')->with($this->inventoryMock)->andReturnNull();

        $this->discountValidator
            ->shouldReceive('validate')
            ->once()
            ->with(10)
            ->andReturnTrue();

        $result = $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);

        self::assertEquals($this->productVariantMock, $result);
    }

    public function testItCanCreateConfirmedInventoryWhenCheckInitialStatusIsFalse(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->inventoryMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(2);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getFinalPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(45000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('isActive')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnTrue();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getMaxPurchasePerOrder')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(6);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getStock')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(100);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSellerCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnNull();

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(3, 1, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturnNull();

        $this->factoryMock->shouldReceive('getInventory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->productVariantMock->shouldReceive('addInventory')
                                 ->once()
                                 ->with($this->inventoryMock)
                                 ->andReturnSelf();

        $this->inventoryMock->shouldReceive('setSeller')
                            ->once()
                            ->with($this->sellerMock)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setFinalPrice')
                            ->once()
                            ->with(45000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setIsActive')
                            ->once()
                            ->with(true)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setMaxPurchasePerOrder')
                            ->once()
                            ->with(6)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerStock')
                            ->once()
                            ->with(100)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setLeadTime')
                            ->once()
                            ->with(2)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::CONFIRMED)
                            ->andReturn($this->inventoryMock);

        $this->inventoryPriceValidator->expects('validate')->with($this->inventoryMock)->andReturnNull();

        $this->discountValidator
            ->shouldReceive('validate')
            ->once()
            ->with(10)
            ->andReturnTrue();

        $result = $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);

        self::assertEquals($this->productVariantMock, $result);
    }

    public function testItCanCreateConfirmedInventoryWhenLeadTimeEqualsToZero(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->inventoryMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(0);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getFinalPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('isActive')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnTrue();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getMaxPurchasePerOrder')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(6);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getStock')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(100);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSellerCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnNull();

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(3, 1, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturnNull();

        $this->factoryMock->shouldReceive('getInventory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->productVariantMock->shouldReceive('addInventory')
                                 ->once()
                                 ->with($this->inventoryMock)
                                 ->andReturnSelf();

        $this->inventoryMock->shouldReceive('setSeller')
                            ->once()
                            ->with($this->sellerMock)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setFinalPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('setIsActive')
                            ->once()
                            ->with(true)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setMaxPurchasePerOrder')
                            ->once()
                            ->with(6)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerStock')
                            ->once()
                            ->with(100)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setLeadTime')
                            ->once()
                            ->with(0)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::WAIT_FOR_CONFIRM)
                            ->andReturn($this->inventoryMock);

        $this->inventoryPriceValidator->expects('validate')->with($this->inventoryMock)->andReturnNull();

        $this->discountValidator
            ->shouldReceive('validate')
            ->once()
            ->with(0)
            ->andReturnTrue();

        $this->createProductVariantWithInventory->setCheckInitialStatus(true);
        $result = $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);

        self::assertEquals($this->productVariantMock, $result);
    }

    public function testItCanCreateWaitForConfirmInventory(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->inventoryMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->productVariantAndInventoryDTOMock->shouldReceive('getProduct')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->productMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getOptionValues')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(new ArrayCollection([
                                                    $this->productOptionValueMock,
                                                    $this->productOptionValueMock
                                                ]));
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSeller')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn($this->sellerMock);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSuppliesIn')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(2);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(50000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getFinalPrice')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(45000);
        $this->productVariantAndInventoryDTOMock->shouldReceive('isActive')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnTrue();
        $this->productVariantAndInventoryDTOMock->shouldReceive('getMaxPurchasePerOrder')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(6);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getStock')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturn(100);
        $this->productVariantAndInventoryDTOMock->shouldReceive('getSellerCode')
                                                ->once()
                                                ->withNoArgs()
                                                ->andReturnNull();

        $this->productOptionValueMock->shouldReceive('getOption')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn($this->productOptionMock);
        $this->productOptionValueMock->shouldReceive('getId')
                                     ->twice()
                                     ->withNoArgs()
                                     ->andReturn(7, 8);

        $this->productOptionMock->shouldReceive('getId')
                                ->times(4)
                                ->withNoArgs()
                                ->andReturn(3, 1, 1, 3);

        $this->productMock->shouldReceive('getOptions')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([
                              $this->productOptionMock,
                              $this->productOptionMock
                          ]));

        $this->productVariantMock->shouldReceive('getId')
                                 ->once()
                                 ->withNoArgs()
                                 ->andReturn(5);

        $this->productVariantRepositoryMock->shouldReceive('findByProductAndOptions')
                                           ->once()
                                           ->with($this->productMock, [7, 8])
                                           ->andReturn($this->productVariantMock);
        $this->productVariantRepositoryMock->shouldReceive('getCategoryLeadValueByVariantId')
                                           ->once()
                                           ->with(5)
                                           ->andReturn(3);

        $this->inventoryRepositoryMock->shouldReceive('findOneBy')
                                      ->once()
                                      ->with(['variant' => $this->productVariantMock, 'seller' => $this->sellerMock])
                                      ->andReturnNull();

        $this->factoryMock->shouldReceive('getInventory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->productVariantMock->shouldReceive('addInventory')
                                 ->once()
                                 ->with($this->inventoryMock)
                                 ->andReturnSelf();

        $this->inventoryMock->shouldReceive('setSeller')
                            ->once()
                            ->with($this->sellerMock)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setPrice')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setFinalPrice')
                            ->once()
                            ->with(45000)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setIsActive')
                            ->once()
                            ->with(true)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setMaxPurchasePerOrder')
                            ->once()
                            ->with(6)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setSellerStock')
                            ->once()
                            ->with(100)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setLeadTime')
                            ->once()
                            ->with(2)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::CONFIRMED)
                            ->andReturn($this->inventoryMock);

        $this->inventoryPriceValidator->expects('validate')->with($this->inventoryMock)->andReturnNull();

        $this->discountValidator
            ->shouldReceive('validate')
            ->once()
            ->with(10)
            ->andReturnTrue();

        $this->createProductVariantWithInventory->setCheckInitialStatus(true);

        $result = $this->createProductVariantWithInventory->perform($this->productVariantAndInventoryDTOMock);

        self::assertEquals($this->productVariantMock, $result);
    }
}
