<?php

namespace App\Service\RateAndReview\Listeners;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\PromotionCoupon;
use App\Repository\ApologyRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\PromotionCouponService;
use App\Service\RateAndReview\Events\RateAndReviewAccepted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendPromotionCouponSmsOnAcceptingReviewListener implements EventSubscriberInterface
{
    public function __construct(
        private PromotionCouponService $promotionCouponService,
        private ApologyRepository $apologyRepository,
        private ConfigurationServiceInterface $configService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RateAndReviewAccepted::class => 'onRateAndReviewAccepted',
        ];
    }

    public function onRateAndReviewAccepted(RateAndReviewAccepted $event): void
    {
        if (null === $id = $this->getConfig(ConfigurationCodeDictionary::RATE_AND_REVIEW_SMS_APOLOGY_ID)) {
            return;
        }

        if (null === $apology = $this->apologyRepository->find($id)) {
            return;
        }

        $coupon = new PromotionCoupon();
        $coupon->setPromotion($apology->getPromotion());

        $couponDTO = new PromotionCouponDTO();
        $couponDTO->addCustomer(
            $event->getReview()->getCustomer()
        );

        $this->promotionCouponService->updateFromDTO($coupon, $couponDTO);
    }

    private function getConfig(string $code): ?int
    {
        $config = $this->configService->findByCode($code);

        return $config ? (int)$config->getValue() : null;
    }
}
