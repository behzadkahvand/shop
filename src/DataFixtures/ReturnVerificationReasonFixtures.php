<?php

namespace App\DataFixtures;

use App\Entity\ReturnVerificationReason;

class ReturnVerificationReasonFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $returnVerificationReason = (new ReturnVerificationReason())->setReason('some dummy reason');

        $this->manager->persist($returnVerificationReason);
        $this->manager->flush();
    }
}
