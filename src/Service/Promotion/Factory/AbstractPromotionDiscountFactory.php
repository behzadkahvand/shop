<?php

namespace App\Service\Promotion\Factory;

use App\Entity\PromotionAction;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

abstract class AbstractPromotionDiscountFactory implements PromotionDiscountFactoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(PromotionAction $action, int $amount, PromotionSubjectInterface $subject): PromotionDiscount
    {
        Assert::isInstanceOf($subject, static::supportedSubjectClass());

        $promotionDiscount = $this->initialize();
        $promotionDiscount
            ->setAction($action)
            ->setAmount($amount)
        ;

        $subject->addDiscount($promotionDiscount);

        $this->entityManager->persist($promotionDiscount);

        return $promotionDiscount;
    }

    abstract protected function initialize(): PromotionDiscount;
}
