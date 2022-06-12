<?php

namespace App\Service\Promotion;

interface PromotionProcessorInterface
{
    public function process(PromotionSubjectInterface $subject, array $context = []): void;

    public function processChangedSubject(PromotionSubjectInterface $subject, array $context = []): void;
}
