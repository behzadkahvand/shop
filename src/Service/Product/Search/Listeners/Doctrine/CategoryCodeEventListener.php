<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Repository\CategoryRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\CategoryNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductSearchDataEventListener
 */
final class CategoryCodeEventListener implements EventSubscriberInterface
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchDataEvent::class       => ['onProductSearchDataEvent', 0],
            SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 0],
        ];
    }

    /**
     * @param AbstractProductSearchDataEvent $event
     *
     * @return void
     * @throws CategoryNotFoundException
     */
    public function onProductSearchDataEvent(AbstractProductSearchDataEvent $event): void
    {
        $filters = $event->getData()->getFilters();

        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN() || !isset($filters['category.code'])) {
            return;
        }

        $categoryCode = $filters['category.code'];

        $category = $this->categoryRepository->findOneBy(['code' => $categoryCode]);

        if (!$category) {
            throw new CategoryNotFoundException();
        }

        $categoryIds = $this->categoryRepository->getCategoryLeafIdsForCategory($category);

        unset($filters['category.code']);

        $filters['category.id']['in'] = $categoryIds;

        $event->setData(new DoctrineSearchData(
            $filters,
            $event->getData()->getSorts(),
            $categoryCode,
            $event->getData()->getTitle()
        ));
    }
}
