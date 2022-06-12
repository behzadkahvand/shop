<?php

namespace App\Tests\Unit\Service\ORM\Filter;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\ORM\Filter\InventoryHasStockFilter;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class InventoryHasStockFilterTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected ClassMetadata|LegacyMockInterface|MockInterface|null $classMetaDataMock;

    protected ?InventoryHasStockFilter $inventoryIsActiveFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->classMetaDataMock = Mockery::mock(ClassMetadata::class);

        $this->inventoryIsActiveFilter = new InventoryHasStockFilter($this->em);
    }

    public function testItCanNotAddInventoryIsActiveFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Product::class);

        $result = $this->inventoryIsActiveFilter->addFilterConstraint($this->classMetaDataMock, 'p0_');

        self::assertEquals('', $result);
    }

    public function testItCanAddInventoryIsActiveFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Inventory::class);

        $result = $this->inventoryIsActiveFilter->addFilterConstraint($this->classMetaDataMock, 'i2_');

        self::assertEquals('i2_.seller_stock > 0', $result);
    }
}
