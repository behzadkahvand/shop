<?php

namespace App\Service\Product\Search\Listeners;

use App\Events\Product\Search\ProductSearchResultEvent;
use App\Service\Product\Logs\SearchLogService;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class LogSearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WebsiteAreaService $websiteAreaService,
        private Security $security,
        private SearchLogService $searchLogService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ProductSearchResultEvent::class => 'onProductSearchResult'];
    }

    public function onProductSearchResult(ProductSearchResultEvent $event): void
    {
        $term = $this->getSearchTerm($event->getSearchData());

        if (!$this->websiteAreaService->isCustomerArea() || !$term) {
            return;
        }

        $currentCustomer = $this->security->getUser();

        $this->searchLogService->dispatchSearchLogMsg(
            $term,
            $event->getSearchResult()->getMetas()['totalItems'] ?? 0,
            $currentCustomer ? $currentCustomer->getId() : null
        );
    }

    private function getSearchTerm($searchData): ?string
    {
        if (!isset($searchData->getFilters()['title']) || empty($searchData->getFilters()['title'])) {
            return null;
        }

        if (is_array($searchData->getFilters()['title']) && !empty($term = array_values($searchData->getFilters()['title'])[0])) {
            return $term;
        }

        if (is_string($term = $searchData->getFilters()['title'])) {
            return $term;
        }

        return null;
    }
}
