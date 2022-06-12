<?php

/**
 * User: amir
 * Date: 12/2/20
 * Time: 11:54 AM
 */

namespace App\Service\Promotion;

use App\Entity\Promotion;
use App\Form\Promotion\PromotionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class PromotionFormSubmissionHandler
{
    private EntityManagerInterface $entityManager;

    private FormFactoryInterface $formFactory;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function submit(Promotion $promotion, $data)
    {
        $form = $this->formFactory->create(PromotionType::class, $promotion);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $promotion->getId()) {
                $this->entityManager->persist($promotion);
            }
            $this->entityManager->flush();
        }

        return $form;
    }
}
