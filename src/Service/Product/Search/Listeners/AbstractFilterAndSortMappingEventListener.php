<?php

namespace App\Service\Product\Search\Listeners;

use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\SearchDataMapping\SearchDataMappingInterface;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractFilterAndSortMappingEventListener implements EventSubscriberInterface
{
    public function __construct(
        protected WebsiteAreaService $websiteAreaService,
        protected SearchDataMappingInterface $mapping
    ) {
    }

    /**
     * @inheritDoc
     */
    abstract public static function getSubscribedEvents(): array;

    public function onProductSearchData(AbstractProductSearchDataEvent $event): void
    {
        $filters = $this->processFilters($event->getData()->getFilters());
        $sorts   = $this->processSorts($event->getData()->getSorts());

        $event->setData(new SearchData($filters, $sorts));
    }

    private function processFilters(array $filters): array
    {
        if (empty($filters)) {
            return $filters;
        }

        return array_combine($this->getMappedKeys($filters), array_values($filters));
    }

    private function getMappedKeys(array $filters): array
    {
        $area = $this->websiteAreaService->getArea();

        return collect($filters)->keys()->map(function ($key) use ($area) {
            if (!$this->mapping->hasMappedFilter($key, $area)) {
                throw new SearchDataValidationException('Product filters is invalid!');
            }

            return $this->mapping->getMappedFilter($key, $area);
        })->toArray();
    }

    private function processSorts(array $sorts): array
    {
        if (empty($sorts)) {
            return $sorts;
        }

        $area = $this->websiteAreaService->getArea();

        foreach (SortParameterNormalizer::toArray($sorts) as $index => $sort) {
            if (!$this->mapping->hasMappedSort($sort['field'], $area)) {
                throw new SearchDataValidationException('Product sorts is invalid!');
            }

            $sorts[$index] = $sort['direction_prefix'] . $this->mapping->getMappedSort($sort['field'], $area);
        }

        return $sorts;
    }
}
