<?php

/**
 * User: amir
 * Date: 12/15/20
 * Time: 11:08 PM
 */

namespace App\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;
use App\Entity\PromotionCoupon;
use App\Repository\PromotionCouponRepository;
use App\Service\Promotion\Exception\FailedGenerationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Webmozart\Assert\Assert;

class PromotionCouponGenerator implements PromotionCouponGeneratorInterface
{
    private const BATCH_SIZE = 100;

    private PromotionCouponRepository $promotionCouponRepository;

    private EntityManagerInterface $entityManager;

    private GenerationPolicyInterface $generationPolicy;

    public function __construct(
        PromotionCouponRepository $promotionCouponRepository,
        EntityManagerInterface $entityManager,
        GenerationPolicyInterface $generationPolicy
    ) {
        $this->promotionCouponRepository = $promotionCouponRepository;
        $this->entityManager = $entityManager;
        $this->generationPolicy = $generationPolicy;
    }

    public function generate(CouponGeneratorInstruction $instruction): array
    {
        $generatedCoupons = [];

        $this->assertGenerationIsPossible($instruction);

        $amount = $instruction->getAmount();
        for ($i = 1; $i <= $amount; ++$i) {
            $code = $this->generateUniqueCode(
                $instruction->getCodeLength(),
                $generatedCoupons,
                $instruction->getPrefix(),
                $instruction->getSuffix()
            );

            $coupon = new PromotionCoupon();
            $coupon->setPromotion($instruction->getPromotion());
            $coupon->setCode($code);
            $coupon->setExpiresAt($instruction->getExpiresAt());
            $coupon->setUsageLimit(1);
            $coupon->setPerCustomerUsageLimit(1);

            $generatedCoupons[$code] = null;

            $this->entityManager->persist($coupon);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(PromotionCoupon::class);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear(PromotionCoupon::class);

        return array_keys($generatedCoupons);
    }

    /**
     * @throws FailedGenerationException
     */
    private function assertGenerationIsPossible(CouponGeneratorInstruction $instruction): void
    {
        if (!$this->generationPolicy->isGenerationPossible($instruction)) {
            throw new FailedGenerationException($instruction);
        }
    }

    /**
     * @param int $codeLength
     * @param array $generatedCoupons
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return string
     *
     * @throws Exception
     */
    private function generateUniqueCode(
        int $codeLength,
        array $generatedCoupons,
        ?string $prefix,
        ?string $suffix
    ): string {
        Assert::nullOrRange($codeLength, 1, 40, 'Invalid %d code length should be between %d and %d');

        do {
            $hash = bin2hex(random_bytes(20));
            $code = $prefix . strtoupper(substr($hash, 0, $codeLength)) . $suffix;
        } while ($this->isUsedCode($code, $generatedCoupons));

        return $code;
    }

    private function isUsedCode(string $code, array $generatedCoupons): bool
    {
        if (isset($generatedCoupons[$code])) {
            return true;
        }

        return null !== $this->promotionCouponRepository->findOneBy(['code' => $code]);
    }
}
