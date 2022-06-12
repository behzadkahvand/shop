<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\Seller;
use App\Serializer\Normalizer\SellerProductNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SellerProductNormalizerTest extends MockeryTestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ObjectNormalizer
     */
    protected $normalizerMock;

    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaServiceMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Security
     */
    protected $securityMock;

    /**
     * @var Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productMock;

    /**
     * @var Inventory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $inventoryMock;

    /**
     * @var Seller|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizerMock         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaServiceMock = Mockery::mock(WebsiteAreaService::class);
        $this->securityMock           = Mockery::mock(Security::class);
        $this->productMock            = Mockery::mock(Product::class);
        $this->inventoryMock          = Mockery::mock(Inventory::class);
        $this->sellerMock             = Mockery::mock(Seller::class);
    }

    protected function tearDown(): void
    {
        $this->normalizerMock         = null;
        $this->websiteAreaServiceMock = null;
        $this->securityMock           = null;
        $this->productMock            = null;
        $this->inventoryMock          = null;
        $this->sellerMock             = null;
    }

    public function testItSupportsNormalizingProducts(): void
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        self::assertTrue($productNormalizer->supportsNormalization($this->productMock, null, [
            'groups' => ['media', 'seller.product.search']
        ]));
    }

    public function testItDoesNotSupportObjectsOtherThanProductEntityInSellerArea(): void
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        self::assertFalse($productNormalizer->supportsNormalization(new stdClass()));
    }

    public function testItDoesNotSupportNormalizingProductEntitiesWhenAreaIsNotSeller(): void
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        self::assertFalse($productNormalizer->supportsNormalization($this->productMock));
    }

    public function testItDoesNotSupportNormalizingProductEntitiesWhenGroupIsInvalid(): void
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        self::assertFalse($productNormalizer->supportsNormalization($this->productMock, null, [
            'groups' => ['seller.order.items.index']
        ]));
    }

    public function testItCanNormalizeProductWithSettingIsSellerFalse(): void
    {
        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->productMock, null, [])
                             ->andReturn([]);

        $this->securityMock->shouldReceive('getUser')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->sellerMock);

        $this->productMock->shouldReceive('getInventories')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([$this->inventoryMock, $this->inventoryMock]));

        $this->inventoryMock->shouldReceive('getSeller')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn($this->sellerMock);

        $this->sellerMock->shouldReceive('getId')
                         ->times(4)
                         ->withNoArgs()
                         ->andReturn(13, 1, 12, 2);

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        $result = $productNormalizer->normalize($this->productMock);

        self::assertArrayHasKey('isSeller', $result);
        self::assertEquals([
            'isSeller'       => false,
            'inventoryCount' => 0,
        ], $result);
    }

    public function testItCanNormalizeProductWithSettingIsSellerTrue(): void
    {
        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->productMock, null, [])
                             ->andReturn([]);

        $this->securityMock->shouldReceive('getUser')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->sellerMock);

        $this->productMock->shouldReceive('getInventories')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([$this->inventoryMock, $this->inventoryMock]));

        $this->inventoryMock->shouldReceive('getSeller')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn($this->sellerMock);

        $this->sellerMock->shouldReceive('getId')
                         ->times(4)
                         ->withNoArgs()
                         ->andReturn(13);

        $productNormalizer = new SellerProductNormalizer(
            $this->normalizerMock,
            $this->websiteAreaServiceMock,
            $this->securityMock
        );

        $result = $productNormalizer->normalize($this->productMock);

        self::assertArrayHasKey('isSeller', $result);
        self::assertEquals([
            'isSeller'       => true,
            'inventoryCount' => 2,
        ], $result);
    }
}
