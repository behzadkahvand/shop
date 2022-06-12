<?php

namespace App\EventSubscriber;

use App\Dictionary\NotificationCodeDictionary;
use App\Entity\Apology;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Repository\ApologyRepository;
use App\Service\Notification\RecipientFactory;
use App\Service\Promotion\Event\CreatedPromotionCouponEvent;
use App\Service\Promotion\Event\CreatingPromotionCouponEvent;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PromotionCouponSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ApologyRepository $apologyRepository,
        private MessageBusInterface $bus,
        private RecipientFactory $recipientFactory
    ) {
    }

    public function promotionCouponIsBeenCreating(CreatingPromotionCouponEvent $event)
    {
        $promotionCoupon = $event->getPromotionCoupon();

        if ($promotionCoupon->getCode()) {
            return;
        }

        $apology = $this->apologyRepository->findOneBy([
            'promotion' => $promotionCoupon->getPromotion(),
        ]);

        if (!$apology) {
            return;
        }

        if (!$promotionCoupon->getExpiresAt()) {
            $promotionCoupon->setExpiresAt(new DateTime('+1 month'));
        }

        $promotionCoupon->setCode($apology->getCodePrefix() . uniqid());
        if (null === $promotionCoupon->getUsageLimit()) {
            $promotionCoupon->setUsageLimit(1);
        }

        if (null === $promotionCoupon->getPerCustomerUsageLimit()) {
            $promotionCoupon->setPerCustomerUsageLimit(1);
        }

        $event->offsetSet('apology', $apology);
    }

    public function promotionCouponHasBeenCreated(CreatedPromotionCouponEvent $event)
    {
        if (!$event->offsetExists('apology') || !$event->offsetGet('apology') instanceof Apology) {
            return;
        }

        /** @var Apology $apology */
        $apology = $event->offsetGet('apology');

        $coupon = $event->getPromotionCoupon();
        foreach ($coupon->getCustomers() as $customer) {
            $this->bus->dispatch(async_message(new SmsNotification(
                $this->recipientFactory->make($customer),
                strtr($apology->getMessageTemplate(), [
                    '%name%'         => $customer->getName(),
                    '%family%'       => $customer->getFamily(),
                    '%code%'         => $coupon->getCode(),
                    '%expires_date%' => $coupon->getExpiresAt()->format('Y-m-d'),
                ]),
                NotificationCodeDictionary::CUSTOMER_PROMOTION_COUPON
            )));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CreatingPromotionCouponEvent::EVENT_NAME => 'promotionCouponIsBeenCreating',
            CreatedPromotionCouponEvent::EVENT_NAME  => 'promotionCouponHasBeenCreated',
        ];
    }
}
