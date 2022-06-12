<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\ProductIdFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductIdFilterTest extends MockeryTestCase
{
    private ?ProductIdFilter $productIdFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productIdFilter = new ProductIdFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->productIdFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.product_id' => 44648]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('inventory.product_id')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('equals')
                          ->once()
                          ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->productIdFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);

        self::assertNotEquals($builderMockBefore, $resultPayload->getQueryBuilder());
    }

    public function testValidateColorFilterWhenValidateNotPassed()
    {
        $requestData = ['filter' => ['inventory_price.wrongKey' => 20]];

        $builderMock = Mockery::mock(Builder::class);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->productIdFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
