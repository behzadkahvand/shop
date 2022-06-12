<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SellerExcludedCategoriesFilterListener implements EventSubscriberInterface
{
    protected WebsiteAreaService $websiteAreaService;

    protected ConfigurationServiceInterface $configurationService;

    public function __construct(WebsiteAreaService $websiteAreaService, ConfigurationServiceInterface $configurationService)
    {
        $this->websiteAreaService   = $websiteAreaService;
        $this->configurationService = $configurationService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1],
        ];
    }

    /**
     * @param ProductSearchDataEvent $event
     */
    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN() || !$this->websiteAreaService->isSellerArea()) {
            return;
        }

        $excludedCategories = $this->getExcludedCategories();

        if (empty($excludedCategories)) {
            return;
        }

        $filters = $event->getData()->getFilters();

        $filters['category.id']['nin'] = implode(',', $excludedCategories);

        $event->setData(new DoctrineSearchData(
            $filters,
            $event->getData()->getSorts(),
            $event->getData()->getCategoryCode(),
            $event->getData()->getTitle()
        ));
    }

    /**
     * @return array<integer>
     */
    protected function getExcludedCategories(): array
    {
        $config = $this->configurationService->findByCode(
            ConfigurationCodeDictionary::SELLER_SEARCH_EXCLUDED_CATEGORIES
        );

        if ($config === null || $config->getValue() === null) {
            return [];
        }

        return array_map(fn($v) => (int)$v, (array)$config->getValue());
    }
}
