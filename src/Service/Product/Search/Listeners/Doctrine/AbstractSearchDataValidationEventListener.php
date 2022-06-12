<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\ORM\Extension\SortParameterNormalizer;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractSearchDataValidationEventListener implements EventSubscriberInterface
{
    protected WebsiteAreaService $websiteAreaService;

    public function __construct(WebsiteAreaService $websiteAreaService)
    {
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [ProductSearchDataEvent::class => ['onProductSearchData', 100]];
    }

    /**
     * @param AbstractProductSearchDataEvent $event
     */
    public function onProductSearchData(AbstractProductSearchDataEvent $event): void
    {
        $filters = $event->getData()->getFilters();
        $sorts   = $event->getData()->getSorts();

        $this->validateFilterKeys($filters);
        $this->validateSorts($sorts);
        $this->validateBrandCode($filters['brand.code'] ?? true);
        $this->validateCategoryCode($filters['category.code'] ?? true);
    }

    /**
     * @param array $filters
     */
    protected function validateFilterKeys(array $filters): void
    {
        $validFilters = $this->getValidFilters();

        $filterKeys = array_keys($filters);

        if (!empty(array_diff($filterKeys, $validFilters))) {
            throw new SearchDataValidationException('Product filters is invalid!');
        }
    }

    /**
     * @param array $sorts
     */
    protected function validateSorts(array $sorts): void
    {
        $validSorts = $this->getValidSorts();

        $sortKeys = array_column(SortParameterNormalizer::toArray($sorts), 'field');

        if (!empty(array_diff($sortKeys, $validSorts))) {
            throw new SearchDataValidationException('Product sorts is invalid!');
        }
    }

    /**
     * @return array
     */
    abstract protected function getValidFilters(): array;

    /**
     * @return array
     */
    abstract protected function getValidSorts(): array;

    /**
     * @param $brandCode
     */
    protected function validateBrandCode($brandCode): void
    {
        if (is_array($brandCode)) {
            throw new SearchDataValidationException('Only valid operator for "brand.code" filter is equality!');
        }
    }

    /**
     * @param $categoryCode
     */
    protected function validateCategoryCode($categoryCode): void
    {
        if (is_array($categoryCode)) {
            throw new SearchDataValidationException('Only valid operator for "category.code" filter is equality!');
        }
    }
}
