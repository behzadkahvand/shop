<?php

namespace App\Tests\Unit\Service\Product\Similar;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Product\Similar\SimilarProductService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SimilarProductServiceTest extends MockeryTestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|NormalizerInterface
     */
    protected $normalizerMock;

    /**
     * @var ProductRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productRepoMock;

    /**
     * @var Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productMock;

    protected SimilarProductService $similarProductService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizerMock  = Mockery::mock(NormalizerInterface::class);
        $this->productRepoMock = Mockery::mock(ProductRepository::class);
        $this->productMock     = Mockery::mock(Product::class);

        $this->similarProductService = new SimilarProductService(
            $this->normalizerMock,
            $this->productRepoMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->similarProductService);

        $this->normalizerMock  = null;
        $this->productRepoMock = null;
        $this->productMock     = null;
    }

    public function testItCanGetSimilarProductsFromDatabase(): void
    {
        $this->productRepoMock->shouldReceive('getSimilarProducts')
                              ->once()
                              ->with($this->productMock)
                              ->andReturn(['products']);

        $this->normalizerMock->shouldReceive('normalize')
                              ->once()
                              ->with(
                                  ['products'],
                                  null,
                                  [
                                      'groups' => ['product.search'],
                                  ]
                              )
                              ->andReturn(['data']);

        $result = $this->similarProductService->getSimilarProducts($this->productMock);

        self::assertEquals(['data'], $result);
    }
}
