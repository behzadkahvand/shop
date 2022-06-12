<?php

namespace App\Tests\Unit\Service\ORM\Filter;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\ORM\Filter\InventoryConfirmedStatusFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryConfirmedStatusFilterTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var ClassMetadata|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $classMetaDataMock;

    protected InventoryConfirmedStatusFilter $inventoryConfirmedStatusFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->classMetaDataMock = Mockery::mock(ClassMetadata::class);

        $this->inventoryConfirmedStatusFilter = new InventoryConfirmedStatusFilter($this->em);
    }

    public function testItCanNotAddInventoryConfirmedStatusFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Product::class);

        $result = $this->inventoryConfirmedStatusFilter->addFilterConstraint($this->classMetaDataMock, 'p0_');

        self::assertEquals('', $result);
    }

    public function testItCanAddInventoryConfirmedStatusFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Inventory::class);

        $result = $this->inventoryConfirmedStatusFilter->addFilterConstraint($this->classMetaDataMock, 'i2_');

        self::assertEquals('i2_.status = "CONFIRMED"', $result);
    }
}
