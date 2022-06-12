<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\IsActiveFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IsActiveFilterTest extends MockeryTestCase
{
    private ?IsActiveFilter $isActiveFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->isActiveFilter = new IsActiveFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->isActiveFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.active' => 1]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('inventory.is_active')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('equals')
                          ->once()
                          ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->isActiveFilter->__invoke($payload);

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

        $resultPayload = $this->isActiveFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
