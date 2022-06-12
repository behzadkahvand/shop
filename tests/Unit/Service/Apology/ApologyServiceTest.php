<?php

namespace App\Tests\Unit\Service\Apology;

use App\Entity\Apology;
use App\Service\Builders\Entity\PromotionCouponBuilder;
use App\Entity\Customer;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Apology\ApologyService;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\Builders\PromotionCouponDTOBuilder;
use App\Service\Promotion\PromotionCouponService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ApologyServiceTest extends MockeryTestCase
{
    /**
     * @var PromotionCouponService|LegacyMockInterface|MockInterface
     */
    private $promotionCouponService;

    /**
     * @var PromotionCouponBuilder|LegacyMockInterface|MockInterface
     */
    private $couponBuilder;

    /**
     * @var PromotionCouponDTOBuilder|LegacyMockInterface|MockInterface
     */
    private $couponDTOBuilder;

    private ApologyService $sut;
    /**
     * @var PromotionCoupon|LegacyMockInterface|MockInterface
     */
    private $promotion;

    private ?array $customers;

    /**
     * @var PromotionCoupon|LegacyMockInterface|MockInterface
     */
    private $coupon;

    /**
     * @var PromotionCouponDTO|LegacyMockInterface|MockInterface
     */
    private $couponDTO;

    /**
     * @var Apology|LegacyMockInterface|MockInterface
     */
    private $apology;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionCouponService = Mockery::mock(PromotionCouponService::class);
        $this->couponBuilder = Mockery::mock(PromotionCouponBuilder::class);
        $this->couponDTOBuilder = Mockery::mock(PromotionCouponDTOBuilder::class);
        $this->apology = Mockery::mock(Apology::class);
        $this->promotion = Mockery::mock(Promotion::class);
        $this->customers = [Mockery::mock(Customer::class)];
        $this->coupon = Mockery::mock(PromotionCoupon::class);
        $this->couponDTO = Mockery::mock(PromotionCouponDTO::class);

        $this->sut = new ApologyService(
            $this->promotionCouponService,
            $this->couponBuilder,
            $this->couponDTOBuilder
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown() ;

        unset($this->sut);
        $this->promotionCouponService = null;
        $this->couponBuilder = null;
        $this->couponDTOBuilder = null;
        $this->apology = null;
        $this->promotion = null;
        $this->customers = null;
        $this->coupon = null;
        $this->couponDTO = null;
    }

    public function testShouldPromoteCustomers(): void
    {
        $this->apology
            ->shouldReceive('getPromotion')
            ->once()->withNoArgs()->andReturn($this->promotion);

        $this->couponBuilder
            ->shouldReceive('withPromotion')
            ->once()->with($this->promotion)->andReturnSelf();
        $this->couponBuilder
            ->shouldReceive('build')
            ->once()->withNoArgs()->andReturn($this->coupon);

        $this->couponDTOBuilder
            ->shouldReceive('withCustomers')
            ->once()->with($this->customers)->andReturnSelf();
        $this->couponDTOBuilder
            ->shouldReceive('build')
            ->once()->withNoArgs()->andReturn($this->couponDTO);

        $this->promotionCouponService
            ->shouldReceive('UpdateFromDTO')
            ->once()->with($this->coupon, $this->couponDTO)->andReturnNull();

        $this->sut->apologize($this->customers, $this->apology);
    }
}
