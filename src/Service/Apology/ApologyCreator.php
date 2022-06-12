<?php

namespace App\Service\Apology;

use App\Entity\Apology;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;

class ApologyCreator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(Promotion $promotion, string $codePrefix, string $messageTemplate)
    {
        $apology = new Apology();
        $apology->setCodePrefix($codePrefix)->setMessageTemplate($messageTemplate)->setPromotion($promotion);

        $this->entityManager->persist($apology);

        return $apology;
    }
}
