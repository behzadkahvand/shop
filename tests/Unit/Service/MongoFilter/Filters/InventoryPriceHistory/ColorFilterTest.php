<?php

namespace App\Tests\Unit\Service\MongoFilter\Filters\InventoryPriceHistory;

use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\Filters\InventoryPriceHistory\ColorFilter;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ColorFilterTest extends MockeryTestCase
{
    private ?ColorFilter $colorFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->colorFilter = new ColorFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->colorFilter = null;
    }

    public function testItCanCallColorFilterWhenValidatePassed()
    {
        $requestData = ['filter' => ['inventory_price.color.id' => 93]];

        $builderMock = Mockery::mock(Builder::class);
        $builderMockBefore = Mockery::mock(Builder::class);

        $builderMock->shouldReceive('field')
                          ->once()
                          ->with('color.id')
                          ->andReturn($builderMock);

        $builderMock->shouldReceive('equals')
                          ->once()
                          ->andReturn($builderMock);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->colorFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);

        self::assertNotEquals($builderMockBefore, $resultPayload->getQueryBuilder());
    }

    public function testValidateColorFilterWhenValidateNotPassed()
    {
        $requestData = ['filter' => ['inventory_price.ColorWrongKey' => 93]];

        $builderMock = Mockery::mock(Builder::class);

        $payload = (new FilterPayload())
            ->setQueryBuilder($builderMock)
            ->setRequestFilters($requestData);

        $resultPayload = $this->colorFilter->__invoke($payload);

        self::assertEquals($payload, $resultPayload);
    }
}
