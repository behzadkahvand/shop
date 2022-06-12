<?php

namespace App\Tests\Unit\Service\ORM\Filter;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\ORM\Filter\InventoryIsActiveFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryIsActiveFilterTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var ClassMetadata|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $classMetaDataMock;

    protected InventoryIsActiveFilter $inventoryIsActiveFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->classMetaDataMock = Mockery::mock(ClassMetadata::class);

        $this->inventoryIsActiveFilter = new InventoryIsActiveFilter($this->em);
    }

    protected function tearDown(): void
    {
        $this->em = null;
        $this->classMetaDataMock = null;

        unset($this->inventoryIsActiveFilter);
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

        self::assertEquals('i2_.is_active = 1', $result);
    }
}
