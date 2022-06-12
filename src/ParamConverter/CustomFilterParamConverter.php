<?php

namespace App\ParamConverter;

use App\Service\ORM\Exceptions\CustomFilterNotFoundException;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomFilterParamConverter
 */
final class CustomFilterParamConverter implements ParamConverterInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * CustomFilterParamConverter constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        $filters = isset($options['filters']) && is_array($options['filters']) ? $options['filters'] : [];

        foreach ($filters as $filter) {
            if (!$this->container->has($filter)) {
                throw new CustomFilterNotFoundException($filter);
            }

            $this->getCustomFilter($filter)->apply($request);
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(ParamConverter $configuration)
    {
        return 'customFilter' === $configuration->getName();
    }

    /**
     * @param string $class
     *
     * @return CustomFilterInterface
     */
    private function getCustomFilter(string $class): CustomFilterInterface
    {
        return $this->container->get($class);
    }
}
