<?php

namespace App\Tests\Unit\Service\Product\Logs;

use App\Service\MongoFilter\PipelineMongoQueryBuilder;
use App\Service\Product\Logs\InventoryPriceHistoryService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Tightenco\Collect\Support\Collection;

class InventoryPriceHistoryServiceTest extends MockeryTestCase
{
    private $pipelineMongoQueryBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pipelineMongoQueryBuilder = Mockery::mock(PipelineMongoQueryBuilder::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testItCanCallFilterPriceHistory()
    {
        $requestFilters = [
            'filter' => [
                'inventory_price.product_id' => 44648,
                'inventory_price.month.min' => 1,
            ]
        ];

        $filterResults = new Collection([]);

        $this->pipelineMongoQueryBuilder->shouldReceive('filter')
                                        ->once()
                                        ->andReturn($filterResults);

        $inventoryPriceHistoryServiceMock = Mockery::mock(InventoryPriceHistoryService::class, [$this->pipelineMongoQueryBuilder])
                                               ->makePartial()
                                               ->shouldAllowMockingProtectedMethods();

        $result = $inventoryPriceHistoryServiceMock->filterPriceHistory($requestFilters);

        $this->assertIsArray($result);
    }
}
