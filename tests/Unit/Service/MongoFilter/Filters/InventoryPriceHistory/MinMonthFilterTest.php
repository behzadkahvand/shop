<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\MinMonthFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MinMonthFilterTest extends MockeryTestCase
{
    private ?MinMonthFilter $minMonthFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minMonthFilter = new MinMonthFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->minMonthFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.month.min' => 3]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('created_at')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('gte')
                          ->once()
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('select')
                    ->once()
                    ->andReturn($builderMock);

        $builderMock->shouldReceive('sort')
                    ->twice()
                    ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->minMonthFilter->__invoke($payload);

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

        $resultPayload = $this->minMonthFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
