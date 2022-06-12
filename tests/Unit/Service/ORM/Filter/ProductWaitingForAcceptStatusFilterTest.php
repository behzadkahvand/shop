<?php

namespace App\Tests\Unit\Service\ORM\Filter;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\ORM\Filter\ProductWaitingForAcceptStatusFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductWaitingForAcceptStatusFilterTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var ClassMetadata|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $classMetaDataMock;

    protected ProductWaitingForAcceptStatusFilter $productIsActiveFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->classMetaDataMock = Mockery::mock(ClassMetadata::class);

        $this->productIsActiveFilter = new ProductWaitingForAcceptStatusFilter($this->em);
    }

    public function testItCanNotAddInventoryIsActiveFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Inventory::class);

        $result = $this->productIsActiveFilter->addFilterConstraint($this->classMetaDataMock, 'i0_');

        self::assertEquals('', $result);
    }

    public function testItCanAddInventoryIsActiveFilter()
    {
        $this->classMetaDataMock->shouldReceive('getName')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(Product::class);

        $result = $this->productIsActiveFilter->addFilterConstraint($this->classMetaDataMock, 'p2_');

        self::assertEquals('p2_.status <> "WAITING_FOR_ACCEPT"', $result);
    }
}
