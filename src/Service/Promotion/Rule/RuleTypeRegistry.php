<?php

namespace App\Service\Promotion\Rule;

use App\Service\Promotion\Rule\RuleTypeRegistryInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class RuleTypeRegistry implements RuleTypeRegistryInterface
{
    private array $ruleTypes;

    /**
     * @var array<string>
     */
    private array $ruleTypeNames = [];

    public function __construct(iterable $ruleTypes)
    {
        $this->ruleTypes = iterator_to_array($ruleTypes);

        foreach ($this->ruleTypes as $name => $class) {
            $this->ruleTypeNames[] = $name;
        }
    }

    public function get(string $name): ?RuleTypeInterface
    {
        if (!isset($this->ruleTypes[$name])) {
            return null;
        }
        return $this->ruleTypes[$name];
    }

    /**
     * @return array<string>
     */
    public function getRuleTypeNames(): array
    {
        return $this->ruleTypeNames;
    }
}
