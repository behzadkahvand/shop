<?php

namespace App\Service\Promotion\Action;

use Symfony\Component\DependencyInjection\ServiceLocator;

class ActionTypeRegistry implements ActionTypeRegistryInterface
{
    private ServiceLocator $actionTypes;

    /**
     * @var array<string>
     */
    private array $actionTypeNames;

    public function __construct(ServiceLocator $actionTypes)
    {
        $this->actionTypes = $actionTypes;

        $this->actionTypeNames = array_keys($actionTypes->getProvidedServices());
    }

    public function get(string $name): ?ActionTypeInterface
    {
        if (!$this->actionTypes->has($name)) {
            return null;
        }

        return $this->actionTypes->get($name);
    }

    public function getActionTypeNames(): array
    {
        return $this->actionTypeNames;
    }
}
