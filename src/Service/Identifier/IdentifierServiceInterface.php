<?php

namespace App\Service\Identifier;

interface IdentifierServiceInterface
{
    public function generateIdentifier($entity): string;
}
