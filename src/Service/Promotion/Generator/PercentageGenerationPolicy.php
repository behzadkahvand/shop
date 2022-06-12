<?php

namespace App\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;
use App\Repository\PromotionCouponRepository;
use Webmozart\Assert\Assert;

final class PercentageGenerationPolicy implements GenerationPolicyInterface
{
    private PromotionCouponRepository $couponRepository;

    private float $ratio;

    public function __construct(PromotionCouponRepository $couponRepository, float $ratio = 0.5)
    {
        $this->couponRepository = $couponRepository;
        $this->ratio = $ratio;
    }

    public function isGenerationPossible(CouponGeneratorInstruction $instruction): bool
    {
        $expectedGenerationAmount = $instruction->getAmount();
        $possibleGenerationAmount = $this->calculatePossibleGenerationAmount($instruction);

        return $possibleGenerationAmount >= $expectedGenerationAmount;
    }

    public function getPossibleGenerationAmount(CouponGeneratorInstruction $instruction): int
    {
        return $this->calculatePossibleGenerationAmount($instruction);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function calculatePossibleGenerationAmount(CouponGeneratorInstruction $instruction): int
    {
        $expectedAmount = $instruction->getAmount();
        $expectedCodeLength = $instruction->getCodeLength();

        Assert::allNotNull(
            [$expectedAmount, $expectedCodeLength],
            'Code length or amount cannot be null.'
        );

        $generatedAmount = $this->couponRepository->countByCodeLength(
            $expectedCodeLength,
            $instruction->getPrefix(),
            $instruction->getSuffix()
        );

        $codeCombination = 16 ** $expectedCodeLength * $this->ratio;
        if ($codeCombination >= \PHP_INT_MAX) {
            return \PHP_INT_MAX - $generatedAmount;
        }

        return (int) $codeCombination - $generatedAmount;
    }
}
