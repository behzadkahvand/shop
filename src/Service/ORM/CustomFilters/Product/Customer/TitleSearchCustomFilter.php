<?php

namespace App\Service\ORM\CustomFilters\Product\Customer;

use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class TitleSearchCustomFilter
 */
final class TitleSearchCustomFilter implements CustomFilterInterface
{
    protected EventDispatcherInterface $dispatcher;

    private WebsiteAreaService $websiteAreaService;

    public function __construct(EventDispatcherInterface $dispatcher, WebsiteAreaService $websiteAreaService)
    {
        $this->dispatcher = $dispatcher;
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();

        if (!isset($queryParams['filter']['title'])) {
            return;
        }

        if (is_array($queryParams['filter']['title'])) {
            $title = current($queryParams['filter']['title']);
        } else {
            $title = $queryParams['filter']['title'];
        }

        // nasty fix here. it is better to move this listener to search listeners
        // Todo: move this class code to a separate product search listener
        if ($this->websiteAreaService->isSellerArea() && preg_match('#^(tpi-)?\d+$#i', $title)) {
            return;
        }

        $callback = function (QueryBuilderFilterAppliedEvent $event) use ($title, &$callback) {
            $this->dispatcher->removeListener(QueryBuilderFilterAppliedEvent::class, $callback);

            $this->applyFilter($event, $title);
        };

        $this->dispatcher->addListener(QueryBuilderFilterAppliedEvent::class, $callback);
    }

    /**
     * @param QueryBuilderFilterAppliedEvent $event
     * @param string $title
     */
    private function applyFilter(QueryBuilderFilterAppliedEvent $event, string $title): void
    {
        $rootAlias    = $event->getRootAlias();
        $queryBuilder = $event->getQueryBuilder();

        $query = '';
        $this->addTitleWhere($queryBuilder, $query, $rootAlias, $title, "{$rootAlias}_title");

//        $chunkTitles = explode(' ', trim($title));
//
//        if (count($chunkTitles) > 1) {
//            $chunkTitles = array_values(array_filter($chunkTitles, fn($chunk) => mb_strlen($chunk) >= 3));
//
//            foreach ($chunkTitles as $key => $chunkTitle) {
//                $this->addTitleWhere($queryBuilder, $query, $rootAlias, $chunkTitle, "{$rootAlias}_title" . ($key + 1));
//            }
//        }

        $queryBuilder->andWhere($query);
    }

    protected function addTitleWhere(QueryBuilder $queryBuilder, string &$query, string $rootAlias, string $title, string $key): void
    {
        $query .= ($query !== '' ? ' OR ' : '')
            . sprintf('%1$s.title LIKE :%2$s OR %1$s.subtitle LIKE :%2$s OR %1$s.additionalTitle LIKE :%2$s', $rootAlias, $key);

        $queryBuilder->setParameter($key, "%$title%");
    }
}
