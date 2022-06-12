<?php

namespace App\Entity;

interface ActivableUserInterface
{
    public function isActive(): bool;
}
