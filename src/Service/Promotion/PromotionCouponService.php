<?php

namespace App\Service\Promotion;

use App\Entity\Customer;
use App\Entity\Promotion;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\Event\CreatedPromotionCouponEvent;
use App\Service\Promotion\Event\CreatingPromotionCouponEvent;
use App\Service\Promotion\Exception\CouponHasEmptyCodeException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PromotionCoupon;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PromotionCouponService
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $dispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function updateFromDTO(PromotionCoupon $coupon, PromotionCouponDTO $couponDTO)
    {
        if ($couponDTO->getExpiresAt()) {
            $coupon->setExpiresAt($couponDTO->getExpiresAt());
        }

        if ($couponDTO->getCode()) {
            $coupon->setCode($couponDTO->getCode());
        }

        $coupon->setUsageLimit($couponDTO->getUsageLimit());
        $coupon->setPerCustomerUsageLimit($couponDTO->getPerCustomerUsageLimit());

        if ($couponDTO->getCustomers()->count() > 0) {
            $coupon->getCustomers()->clear();
            /** @var Customer $customer */
            foreach ($couponDTO->getCustomers() as $customer) {
                $coupon->addCustomer($customer);
            }
        }

        $creatingEvent = new CreatingPromotionCouponEvent($coupon);
        $this->dispatcher->dispatch($creatingEvent, CreatingPromotionCouponEvent::EVENT_NAME);

        if (empty($coupon->getCode())) {
            throw new CouponHasEmptyCodeException();
        }

        $this->entityManager->persist($coupon);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new CreatedPromotionCouponEvent($coupon, $creatingEvent->getArguments()),
            CreatedPromotionCouponEvent::EVENT_NAME
        );

        return $coupon;
    }
}
