<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\MaxMonthFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MaxMonthFilterTest extends MockeryTestCase
{
    private ?MaxMonthFilter $maxMonthFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->maxMonthFilter = new MaxMonthFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->maxMonthFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.month.max' => 1]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('created_at')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('lte')
                          ->once()
                          ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->maxMonthFilter->__invoke($payload);

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

        $resultPayload = $this->maxMonthFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
