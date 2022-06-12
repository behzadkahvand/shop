<?php

namespace App\Service\Promotion\Action;

interface ActionTypeRegistryInterface
{
    public function get(string $name): ?ActionTypeInterface;

    /**
     * @return array<string>
     */
    public function getActionTypeNames(): array;
}
