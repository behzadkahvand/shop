<?php

namespace App\Tests\Unit\Service\ProductAttribute;

use App\DTO\Admin\AttributeValueData;
use App\Entity\ProductAttributeNumericValue;
use App\Entity\ProductAttributeTextValue;
use App\Service\ProductAttribute\Exceptions\RequiredFieldIsNotSetException;
use App\Service\ProductAttribute\ProductAttributeFactory;
use App\Service\ProductAttribute\ProductAttributeValueFactory;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Attribute;
use App\Entity\AttributeGroup;
use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Repository\ProductAttributeRepository;
use App\Service\ProductAttribute\DTO\ProductAttributeGroupData;
use App\Service\ProductAttribute\ProductAttributeService;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAttributeServiceTest extends MockeryTestCase
{
    public function testGetProductAttributesWithGroup()
    {
        $categoryMock = Mockery::mock(Category::class);

        $productMock = Mockery::mock(Product::class);
        $productMock->shouldReceive('getCategory')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($categoryMock);

        $attributeGroupMock = Mockery::mock(AttributeGroup::class);
        $attributeGroupMock->shouldReceive('getId')
                           ->twice()
                           ->withNoArgs()
                           ->andReturn(1);

        $categoryAttributeMock1 = Mockery::mock(CategoryAttribute::class);
        $categoryAttributeMock1->shouldReceive('getPriority')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(1);
        $categoryAttributeMock1->shouldReceive('getAttributeGroup')
                               ->twice()
                               ->withNoArgs()
                               ->andReturn($attributeGroupMock);

        $categoryAttributeMock2 = Mockery::mock(CategoryAttribute::class);
        $categoryAttributeMock2->shouldReceive('getPriority')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(1);
        $categoryAttributeMock2->shouldReceive('getAttributeGroup')
                               ->twice()
                               ->withNoArgs()
                               ->andReturn($attributeGroupMock);

        $attributeMock1 = Mockery::mock(Attribute::class);
        $attributeMock1->shouldReceive('getCategoryAttributes')
                       ->withNoArgs()
                       ->times(3)
                       ->andReturn(new ArrayCollection([$categoryAttributeMock1]));

        $attributeMock2 = Mockery::mock(Attribute::class);
        $attributeMock2->shouldReceive('getCategoryAttributes')
                       ->withNoArgs()
                       ->times(3)
                       ->andReturn(new ArrayCollection([$categoryAttributeMock2]));

        $productAttributeMock1 = Mockery::mock(ProductAttribute::class);
        $productAttributeMock1->shouldReceive('getAttribute')
                              ->times(3)
                              ->withNoArgs()
                              ->andReturn($attributeMock1);

        $productAttributeMock2 = Mockery::mock(ProductAttribute::class);
        $productAttributeMock2->shouldReceive('getAttribute')
                              ->times(3)
                              ->withNoArgs()
                              ->andReturn($attributeMock2);

        $productAttributeRepository = Mockery::mock(ProductAttributeRepository::class);
        $productAttributeRepository->shouldReceive('getProductAttributesWithGroup')
                                   ->once()
                                   ->with($productMock, $categoryMock)
                                   ->andReturn([$productAttributeMock1, $productAttributeMock2]);

        $productAttributeService = new ProductAttributeService(
            $productAttributeRepository,
            Mockery::mock(EntityManager::class),
            Mockery::mock(ProductAttributeFactory::class),
            Mockery::mock(ProductAttributeValueFactory::class)
        );
        $result                  = $productAttributeService->getProductAttributesWithGroup($productMock);

        self::assertIsArray($result);
        self::assertInstanceOf(ProductAttributeGroupData::class, $result[0]);
    }

    public function testUpdateProductAttributesThrowRequiredException(): void
    {
        $product = Mockery::mock(Product::class);

        $categoryAttribute = Mockery::mock(CategoryAttribute::class);

        $categoryAttribute->shouldReceive('getIsRequired')
                          ->once()
                          ->withNoArgs()
                          ->andReturnTrue();

        $attribute = Mockery::mock(Attribute::class);
        $attribute->shouldReceive('getCategoryAttributes')
                  ->once()
                  ->withNoArgs()
                  ->andReturn(new ArrayCollection([$categoryAttribute]));

        $attributeValueData = Mockery::mock(AttributeValueData::class);
        $attributeValueData->shouldReceive('getValue')
                           ->once()
                           ->withNoArgs()
                           ->andReturnNull();
        $attributeValueData->shouldReceive('getAttribute')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($attribute);
        $productAttributeRepository = Mockery::mock(ProductAttributeRepository::class);
        $productAttributeRepository->shouldReceive('findBy')
                                   ->once()
                                   ->with(['product' => $product])
                                   ->andReturn([]);
        $productAttributeService = new ProductAttributeService(
            $productAttributeRepository,
            Mockery::mock(EntityManager::class),
            Mockery::mock(ProductAttributeFactory::class),
            Mockery::mock(ProductAttributeValueFactory::class)
        );

        self::expectException(RequiredFieldIsNotSetException::class);

        $productAttributeService->updateProductAttributes($product, [$attributeValueData]);
    }

    public function testUpdateProductAttributeWithSingleAndNotEmptyValue(): void
    {
        $attributeValue = 12;

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')
                ->once()
                ->withNoArgs()
                ->andReturn(1);

        $attribute = Mockery::mock(Attribute::class);

        $attributeValueData = Mockery::mock(AttributeValueData::class);
        $attributeValueData->shouldReceive('getValue')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn($attributeValue);
        $attributeValueData->shouldReceive('getAttribute')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn($attribute);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getAttribute')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($attribute);

        $productAttributeRepository = Mockery::mock(ProductAttributeRepository::class);
        $productAttributeRepository->shouldReceive('findBy')
                                   ->once()
                                   ->with(['product' => $product])
                                   ->andReturn([$productAttribute]);

        $manager = Mockery::mock(EntityManager::class);
        $manager->shouldReceive('remove')
                ->once()
                ->with($productAttribute)
                ->andReturn();
        $manager->shouldReceive('persist')
                ->once()
                ->with(Mockery::type(ProductAttribute::class))
                ->andReturn();
        $manager->shouldReceive('flush')
                ->once()
                ->withNoArgs()
                ->andReturn();

        $cacheDriver = Mockery::mock(Cache::class);
        $cacheDriver->shouldReceive('delete')
                    ->once()
                    ->with(Mockery::type('string'))
                    ->andReturnTrue();

        $configuration = Mockery::mock(Configuration::class);
        $configuration->shouldReceive('getResultCacheImpl')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($cacheDriver);

        $manager->shouldReceive('getConfiguration')
                ->once()
                ->withNoArgs()
                ->andReturn($configuration);

        $productAttributeValueObject = Mockery::mock(ProductAttributeNumericValue::class);
        $productAttributeValueObject->shouldReceive('setValue')
                                    ->once()
                                    ->with($attributeValue)
                                    ->andReturn($productAttributeValueObject);

        $newProductAttribute = Mockery::mock(ProductAttribute::class);
        $newProductAttribute->shouldReceive('setProduct')
                            ->once()
                            ->with($product)
                            ->andReturn($newProductAttribute);
        $newProductAttribute->shouldReceive('setAttribute')
                            ->once()
                            ->with($attribute)
                            ->andReturn($newProductAttribute);
        $newProductAttribute->shouldReceive('setValue')
                            ->once()
                            ->with($productAttributeValueObject)
                            ->andReturn($newProductAttribute);

        $productAttributeFactory = Mockery::mock(ProductAttributeFactory::class);
        $productAttributeFactory->shouldReceive('create')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($newProductAttribute);

        $productAttributeValueFactory = Mockery::mock(ProductAttributeValueFactory::class);
        $productAttributeValueFactory->shouldReceive('create')
                                     ->once()
                                     ->with($attribute)
                                     ->andReturn($productAttributeValueObject);

        $productAttributeService = new ProductAttributeService(
            $productAttributeRepository,
            $manager,
            $productAttributeFactory,
            $productAttributeValueFactory
        );
        try {
            $productAttributeService->updateProductAttributes($product, [$attributeValueData]);
        } catch (\Throwable $exception) {
            self::fail();
        }

        self::assertTrue(true);
    }

    public function testUpdateProductAttributeWithMultiAndNotEmptyValue(): void
    {
        $attributeValue = ['test1', 'test2'];

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')
                ->once()
                ->withNoArgs()
                ->andReturn(1);

        $attribute = Mockery::mock(Attribute::class);

        $attributeValueData = Mockery::mock(AttributeValueData::class);
        $attributeValueData->shouldReceive('getValue')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn($attributeValue);
        $attributeValueData->shouldReceive('getAttribute')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn($attribute);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getAttribute')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($attribute);

        $productAttributeRepository = Mockery::mock(ProductAttributeRepository::class);
        $productAttributeRepository->shouldReceive('findBy')
                                   ->once()
                                   ->with(['product' => $product])
                                   ->andReturn([$productAttribute]);

        $manager = Mockery::mock(EntityManager::class);
        $manager->shouldReceive('remove')
                ->once()
                ->with($productAttribute)
                ->andReturn();
        $manager->shouldReceive('persist')
                ->once()
                ->with(Mockery::type(ProductAttribute::class))
                ->andReturn();
        $manager->shouldReceive('flush')
                ->once()
                ->withNoArgs()
                ->andReturn();

        $cacheDriver = Mockery::mock(Cache::class);
        $cacheDriver->shouldReceive('delete')
                    ->once()
                    ->with(Mockery::type('string'))
                    ->andReturnTrue();

        $configuration = Mockery::mock(Configuration::class);
        $configuration->shouldReceive('getResultCacheImpl')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($cacheDriver);

        $manager->shouldReceive('getConfiguration')
                ->once()
                ->withNoArgs()
                ->andReturn($configuration);

        $productAttributeValueObject1 = Mockery::mock(ProductAttributeTextValue::class);
        $productAttributeValueObject1->shouldReceive('setValue')
                                     ->once()
                                     ->with($attributeValue[0])
                                     ->andReturn();

        $productAttributeValueObject2 = Mockery::mock(ProductAttributeTextValue::class);
        $productAttributeValueObject2->shouldReceive('setValue')
                                     ->once()
                                     ->with($attributeValue[1])
                                     ->andReturn();

        $newProductAttribute = Mockery::mock(ProductAttribute::class);
        $newProductAttribute->shouldReceive('setProduct')
                            ->once()
                            ->with($product)
                            ->andReturn($newProductAttribute);
        $newProductAttribute->shouldReceive('setAttribute')
                            ->once()
                            ->with($attribute)
                            ->andReturn($newProductAttribute);
        $newProductAttribute->shouldReceive('setValue')
                            ->once()
                            ->with(Mockery::type('array'))
                            ->andReturn($newProductAttribute);

        $productAttributeFactory = Mockery::mock(ProductAttributeFactory::class);
        $productAttributeFactory->shouldReceive('create')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($newProductAttribute);

        $productAttributeValueFactory = Mockery::mock(ProductAttributeValueFactory::class);
        $productAttributeValueFactory->shouldReceive('create')
                                     ->once()
                                     ->with($attribute)
                                     ->andReturn($productAttributeValueObject1);
        $productAttributeValueFactory->shouldReceive('create')
                                     ->once()
                                     ->with($attribute)
                                     ->andReturn($productAttributeValueObject2);

        $productAttributeService = new ProductAttributeService(
            $productAttributeRepository,
            $manager,
            $productAttributeFactory,
            $productAttributeValueFactory
        );
        $productAttributeService->updateProductAttributes($product, [$attributeValueData]);
        try {
        } catch (\Throwable $exception) {
            self::fail();
        }

        self::assertTrue(true);
    }

    public function testUpdateProductAttributeWithEmptyValue(): void
    {
        $attributeId = 1;

        $product = Mockery::mock(Product::class);

        $product->shouldReceive('getId')
                ->once()
                ->withNoArgs()
                ->andReturn(1);

        $categoryAttribute = Mockery::mock(CategoryAttribute::class);

        $categoryAttribute->shouldReceive('getIsRequired')
                          ->once()
                          ->withNoArgs()
                          ->andReturnFalse();

        $attribute = Mockery::mock(Attribute::class);
        $attribute->shouldReceive('getCategoryAttributes')
                  ->once()
                  ->withNoArgs()
                  ->andReturn(new ArrayCollection([$categoryAttribute]));

        $attributeValueData = Mockery::mock(AttributeValueData::class);
        $attributeValueData->shouldReceive('getValue')
                           ->twice()
                           ->withNoArgs()
                           ->andReturnNull();
        $attributeValueData->shouldReceive('getAttribute')
                           ->twice()
                           ->withNoArgs()
                           ->andReturn($attribute);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getAttribute')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($attribute);

        $productAttributeRepository = Mockery::mock(ProductAttributeRepository::class);
        $productAttributeRepository->shouldReceive('findBy')
                                   ->once()
                                   ->with(['product' => $product])
                                   ->andReturn([$productAttribute]);

        $manager = Mockery::mock(EntityManager::class);
        $manager->shouldReceive('remove')
                ->once()
                ->with($productAttribute)
                ->andReturn();

        $manager->shouldReceive('flush')
                ->once()
                ->withNoArgs()
                ->andReturn();

        $cacheDriver = Mockery::mock(Cache::class);
        $cacheDriver->shouldReceive('delete')
                    ->once()
                    ->with(Mockery::type('string'))
                    ->andReturnTrue();

        $configuration = Mockery::mock(Configuration::class);
        $configuration->shouldReceive('getResultCacheImpl')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($cacheDriver);

        $manager->shouldReceive('getConfiguration')
                ->once()
                ->withNoArgs()
                ->andReturn($configuration);

        $productAttributeService = new ProductAttributeService(
            $productAttributeRepository,
            $manager,
            Mockery::mock(ProductAttributeFactory::class),
            Mockery::mock(ProductAttributeValueFactory::class)
        );
        try {
            $productAttributeService->updateProductAttributes($product, [$attributeValueData]);
        } catch (\Throwable $exception) {
            self::fail();
        }

        self::assertTrue(true);
    }
}
