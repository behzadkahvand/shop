<?php

namespace App\Service\Promotion\Rule;

interface RuleTypeRegistryInterface
{
    public function get(string $name): ?RuleTypeInterface;

    /**
     * @return array<string>
     */
    public function getRuleTypeNames(): array;
}
