<?php

namespace App\EventSubscriber;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Seller;
use Doctrine\ORM\EntityManagerInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_PRODUCT_STATUSES = [
        ProductStatusDictionary::CONFIRMED,
        ProductStatusDictionary::SOON,
        ProductStatusDictionary::UNAVAILABLE,
        ProductStatusDictionary::SHUTDOWN,
    ];

    protected EntityManagerInterface $manager;

    private UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $manager, UrlGeneratorInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $urlContainer = $event->getUrlContainer();

        $this->registerBrandUrls($urlContainer);
        $this->registerCategoryUrls($urlContainer);
        $this->registerProductUrls($urlContainer);
        $this->registerSellerUrls($urlContainer);
    }

    private function registerBrandUrls(UrlContainerInterface $urlContainer): void
    {
        $query = $this->manager->createQuery('select r from App\Entity\Brand r');
        $iterableBrands = $query->toIterable();

        foreach ($iterableBrands as $brand) {
            /** @var Brand $brand */
            $urlContainer->addUrl(
                $this->generateUrl("brands/{$brand->getCode()}"),
                'brand'
            );

            $this->manager->clear();
        }
    }

    private function registerCategoryUrls(UrlContainerInterface $urlContainer): void
    {
        $query = $this->manager->createQuery('select r from App\Entity\Category r');
        $iterableCategories = $query->toIterable();

        foreach ($iterableCategories as $category) {
            /** @var Category $category */
            $urlContainer->addUrl(
                $this->generateUrl("search/category-{$category->getCode()}"),
                'category'
            );

            $this->manager->clear();
        }
    }

    private function registerProductUrls(UrlContainerInterface $urlContainer): void
    {
        $query = $this->manager->createQuery('select r from App\Entity\Product r');
        $iterableProducts = $query->toIterable();

        foreach ($iterableProducts as $product) {
            /** @var Product $product */
            if (! $product->getIsActive()) {
                continue;
            }

            if (! in_array($product->getStatus(), self::ALLOWED_PRODUCT_STATUSES, true)) {
                continue;
            }

//            if ($product[0]->getInventories()->count() < 1) {
//                continue;
//            }

            $urlContainer->addUrl(
                $this->generateUrl("product/tpi-{$product->getId()}"),
                'product'
            );

            $this->manager->clear();
        }
    }

    private function registerSellerUrls(UrlContainerInterface $urlContainer): void
    {
        $query = $this->manager->createQuery('select r from App\Entity\Seller r');
        $iterableCategories = $query->toIterable();

        foreach ($iterableCategories as $seller) {
            /** @var Seller $seller */
            $urlContainer->addUrl(
                $this->generateUrl("seller/{$seller->getIdentifier()}"),
                'seller'
            );

            $this->manager->clear();
        }
    }

    private function generateUrl(string $params): UrlConcrete
    {
        $scheme = $this->router->getContext()->getScheme();
        $host = $this->router->getContext()->getHost();

        return new UrlConcrete(
            sprintf('%s://%s%s%s', $scheme, $host, DIRECTORY_SEPARATOR, $params)
        );
    }
}
