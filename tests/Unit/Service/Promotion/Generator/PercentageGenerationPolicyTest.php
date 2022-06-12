<?php

namespace App\Tests\Unit\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;
use App\Repository\PromotionCouponRepository;
use App\Service\Promotion\Generator\PercentageGenerationPolicy;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PercentageGenerationPolicyTest extends MockeryTestCase
{
    private PercentageGenerationPolicy $sut;

    private LegacyMockInterface|PromotionCouponRepository|MockInterface|null $repository;

    private CouponGeneratorInstruction|LegacyMockInterface|MockInterface|null $instruction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository  = Mockery::mock(PromotionCouponRepository::class);
        $this->instruction = Mockery::mock(CouponGeneratorInstruction::class);

        $this->sut = new PercentageGenerationPolicy($this->repository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->repository  = null;
        $this->instruction = null;

        Mockery::close();
    }

    public function testGetPossibleGenerationAmount(): void
    {
        $length = 5;
        $prefix = 'a';
        $suffix = 'z';
        $this->instruction->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(10);
        $this->instruction->shouldReceive('getCodeLength')->once()->withNoArgs()->andReturn($length);
        $this->instruction->shouldReceive('getPrefix')->once()->withNoArgs()->andReturn($prefix);
        $this->instruction->shouldReceive('getSuffix')->once()->withNoArgs()->andReturn($suffix);

        $this->repository->shouldReceive('countByCodeLength')->with($length, $prefix, $suffix)->andReturn(0);

        $possibleGenerationAmount = $this->sut->getPossibleGenerationAmount($this->instruction);

        self::assertEquals(524288, $possibleGenerationAmount);
    }

    public function testIsGenerationPossible(): void
    {
        $length = 5;
        $prefix = 'a';
        $suffix = 'z';
        $this->instruction->shouldReceive('getAmount')->twice()->withNoArgs()->andReturn(10);
        $this->instruction->shouldReceive('getCodeLength')->once()->withNoArgs()->andReturn($length);
        $this->instruction->shouldReceive('getPrefix')->once()->withNoArgs()->andReturn($prefix);
        $this->instruction->shouldReceive('getSuffix')->once()->withNoArgs()->andReturn($suffix);

        $this->repository->shouldReceive('countByCodeLength')->with($length, $prefix, $suffix)->andReturn(0);

        self::assertTrue($this->sut->isGenerationPossible($this->instruction));
    }

    public function testIsGenerationPossibleFalse(): void
    {
        $length = 5;
        $prefix = 'a';
        $suffix = 'z';
        $this->instruction->shouldReceive('getAmount')->twice()->withNoArgs()->andReturn(524289);
        $this->instruction->shouldReceive('getCodeLength')->once()->withNoArgs()->andReturn($length);
        $this->instruction->shouldReceive('getPrefix')->once()->withNoArgs()->andReturn($prefix);
        $this->instruction->shouldReceive('getSuffix')->once()->withNoArgs()->andReturn($suffix);

        $this->repository->shouldReceive('countByCodeLength')->with($length, $prefix, $suffix)->andReturn(0);

        self::assertFalse($this->sut->isGenerationPossible($this->instruction));
    }
}
