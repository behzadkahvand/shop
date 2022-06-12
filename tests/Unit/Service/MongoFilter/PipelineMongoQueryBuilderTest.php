<?php

namespace App\Tests\Unit\Service\MongoFilter;

use App\Document\InventoryPriceHistoryLog;
use App\Service\MongoFilter\FilterPayload;
use App\Service\MongoFilter\PipelineMongoQueryBuilder;
use App\Service\Pipeline\PipelineStageInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Tightenco\Collect\Support\Collection;

class PipelineMongoQueryBuilderTest extends MockeryTestCase
{
    /**
     * @var Builder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $builderMock;

    /**
     * @var DocumentManager|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $managerMock;

    /**
     * @var PipelineStageInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $pipelineStageMock;

    protected $pipelineMongoQueryBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderMock       = Mockery::mock(Builder::class);
        $this->managerMock       = Mockery::mock(DocumentManager::class);
        $this->pipelineStageMock = Mockery::mock(PipelineStageInterface::class);

        $this->pipelineMongoQueryBuilder = Mockery::mock(
            PipelineMongoQueryBuilder::class,
            [[$this->pipelineStageMock], $this->managerMock]
        )
                                                  ->makePartial()
                                                  ->shouldAllowMockingProtectedMethods();

        $this->pipelineMongoQueryBuilder->shouldReceive('makeResult')
                                        ->once()
                                        ->with(Mockery::type(FilterPayload::class))
                                        ->andReturn(new Collection());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->pipelineMongoQueryBuilder = null;
        $this->builderMock               = null;
        $this->managerMock               = null;
        $this->pipelineStageMock         = null;
    }

    public function testItCanCallFilter(): void
    {
        $sourceClass = InventoryPriceHistoryLog::class;

        $this->managerMock->shouldReceive('createQueryBuilder')
                          ->once()
                          ->with($sourceClass)
                          ->andReturn($this->builderMock);

        $this->pipelineStageMock->shouldReceive('__invoke')
                                ->once()
                                ->with(Mockery::type(FilterPayload::class))
                                ->andReturnUsing(function (FilterPayload $payload) {
                                    return $payload->setQueryBuilder($this->builderMock);
                                });

        $requestData = [
            'filter' => [
                'inventory_price.product_id' => 44648,
                'inventory_price.month.min'  => 1,
            ]
        ];

        $result = $this->pipelineMongoQueryBuilder->filter($sourceClass, $requestData);

        self::assertInstanceOf(Collection::class, $result);
    }
}
