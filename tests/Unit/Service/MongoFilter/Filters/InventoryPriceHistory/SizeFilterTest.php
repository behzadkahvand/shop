<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\SizeFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SizeFilterTest extends MockeryTestCase
{
    private ?SizeFilter $sizeFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sizeFilter = new SizeFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->sizeFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.size.id' => 167]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('size.id')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('equals')
                          ->once()
                          ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->sizeFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);

        self::assertNotEquals($builderMockBefore, $resultPayload->getQueryBuilder());
    }

    public function testValidateColorFilterWhenValidateNotPassed()
    {
        $requestData = ['filter' => ['inventory_price.wronggggg' => 20]];

        $builderMock = Mockery::mock(Builder::class);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->sizeFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
