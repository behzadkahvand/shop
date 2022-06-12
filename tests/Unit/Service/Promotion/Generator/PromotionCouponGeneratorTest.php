<?php

namespace App\Tests\Unit\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Repository\PromotionCouponRepository;
use App\Service\Promotion\Generator\GenerationPolicyInterface;
use App\Service\Promotion\Generator\PromotionCouponGenerator;
use DateTime;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionCouponGeneratorTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testGenerate(): void
    {
        $generationPolicy = Mockery::mock(GenerationPolicyInterface::class);
        $repository       = Mockery::mock(PromotionCouponRepository::class);
        $entityManager    = Mockery::mock(EntityManager::class);

        $sut = new PromotionCouponGenerator($repository, $entityManager, $generationPolicy);

        $instruction = Mockery::mock(CouponGeneratorInstruction::class);
        $instruction->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(250);
        $instruction->shouldReceive('getCodeLength')->times(250)->withNoArgs()->andReturn(5);
        $instruction->shouldReceive('getPrefix')->times(250)->withNoArgs()->andReturn('a_');
        $instruction->shouldReceive('getSuffix')->times(250)->withNoArgs()->andReturn('_z');
        $instruction->shouldReceive('getPromotion')->times(250)->withNoArgs()->andReturn(new Promotion());
        $instruction->shouldReceive('getExpiresAt')->times(250)->withNoArgs()->andReturn(new DateTime('+1 month'));

        $repository->shouldReceive('findOneBy')->times(250)->andReturnNull();

        $entityManager->shouldReceive('persist')->times(250);
        $entityManager->shouldReceive('flush')->times(3)->withNoArgs();
        $entityManager->shouldReceive('clear')->times(3)->with(PromotionCoupon::class);

        $generationPolicy->shouldReceive('isGenerationPossible')->once()->with($instruction)->andReturnTrue();

        $sut->generate($instruction);
    }
}
