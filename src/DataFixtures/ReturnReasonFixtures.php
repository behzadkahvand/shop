<?php

namespace App\DataFixtures;

use App\Entity\ReturnReason;

class ReturnReasonFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $returnReason = (new ReturnReason())->setReason('some dummy reason');

        $this->manager->persist($returnReason);
        $this->manager->flush();
    }
}
