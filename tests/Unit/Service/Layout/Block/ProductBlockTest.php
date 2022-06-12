<?php

namespace App\Tests\Unit\Service\Layout\Block;

use App\Repository\ProductRepository;
use App\Service\Layout\Block\ProductBlock;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProductBlockTest
 */
final class ProductBlockTest extends MockeryTestCase
{
    private $productRepository;

    private $em;

    public function testItCanGenerateByProductIds(): void
    {
        $productIds = [1, 2, 3];

        $this->productRepository->shouldReceive('listByIds')
                                ->once()
                                ->with($productIds)
                                ->andReturn([new \stdClass(), new \stdClass(), new \stdClass()]);

        $this->em
            ->shouldReceive('clear')
            ->once()
            ->withNoArgs()
            ->andReturn();

        $block = new ProductBlock($this->productRepository, $this->em);

        self::assertEquals(
            [new \stdClass(), new \stdClass(), new \stdClass()],
            $block->generate(['products' => $productIds])
        );
    }

    public function testItGetCacheSignature()
    {
        $block = new ProductBlock($this->productRepository, $this->em);

        self::assertEquals('1_2_3', $block->getCacheSignature(['products' => [1, 2, 3]]));
    }

    public function testItGetCode(): void
    {
        $block = new ProductBlock($this->productRepository, $this->em);

        self::assertEquals('products', $block->getCode());
    }

    public function testItGetCacheExpiry(): void
    {
        $block = new ProductBlock($this->productRepository, $this->em);

        self::assertEquals(360, $block->getCacheExpiry());
    }

    protected function mockeryTestSetUp(): void
    {
        $this->productRepository = \Mockery::mock(ProductRepository::class);
        $this->em                = \Mockery::mock(EntityManagerInterface::class);
    }

    protected function mockeryTestTearDown()
    {
        \Mockery::close();
    }
}
