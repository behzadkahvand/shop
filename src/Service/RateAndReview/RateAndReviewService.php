<?php

namespace App\Service\RateAndReview;

use App\Dictionary\RateAndReviewStatus;
use App\Entity\Customer;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\RateAndReview;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\RateAndReviewRepository;
use App\Service\RateAndReview\Exceptions\DuplicateRateAndReviewException;
use App\Service\RateAndReview\Statistics\RateAndReviewStatisticsServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class RateAndReviewService
{
    protected ProductRepository $productRepository;

    protected OrderRepository $orderRepository;

    protected EntityManagerInterface $manager;

    protected RateAndReviewRepository $rateAndReviewRepository;

    protected RateAndReviewStatisticsServiceInterface $rateAndReviewStatistics;

    public function __construct(
        EntityManagerInterface $manager,
        RateAndReviewRepository $rateAndReviewRepository,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        RateAndReviewStatisticsServiceInterface $rateAndReviewStatistics
    ) {
        $this->manager = $manager;
        $this->rateAndReviewRepository = $rateAndReviewRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->rateAndReviewStatistics = $rateAndReviewStatistics;
    }

    public function getFindAllAcceptedRateAndReviewsQueryForProduct(Product $product): QueryBuilder
    {
        return $this->rateAndReviewRepository->getFindAllAcceptedRateAndReviewsQueryForProduct($product);
    }

    public function getFindAllCustomerRateAndReviewsQuery(Customer $customer): QueryBuilder
    {
        return $this->rateAndReviewRepository->getFindAllCustomerRateAndReviewsQuery($customer);
    }

    public function getFindAllBoughtProductsWithNoRateAndReviewQuery(Customer $customer): QueryBuilder
    {
        return $this->productRepository->findProductsForAllCustomerDeliveredOrders($customer);
    }

    public function getRateAndReviewStatisticsPerProduct(Product $product): array
    {
        return $this->rateAndReviewStatistics->getStatistics($product);
    }

    public function userAlreadyHasReviewOnProduct(Product $product, Customer $customer): bool
    {
        $rateAndReview = $this->rateAndReviewRepository->findCustomerRateAndReviewOnProduct($customer, $product);

        return $rateAndReview !== null;
    }

    public function addRateAndReview(RateAndReview $rateAndReview, Product $product, Customer $customer): void
    {
        if ($this->userAlreadyHasReviewOnProduct($product, $customer)) {
            throw new DuplicateRateAndReviewException();
        }

        [$order, $inventory] = $this->getOrderAndInventoryByProduct($product, $customer);

        $rateAndReview
            ->setProduct($product)
            ->setCustomer($customer)
            ->setOrder($order)
            ->setInventory($inventory);

        $this->manager->persist($rateAndReview);
        $this->manager->flush();
    }

    public function updateRateAndReview(RateAndReview $rateAndReview): void
    {
        $rateAndReview->setStatus(RateAndReviewStatus::WAIT_FOR_ACCEPT);

        $this->manager->persist($rateAndReview);
        $this->manager->flush();
    }

    public function deleteRateAndReview(RateAndReview $rateAndReview): void
    {
        $this->manager->remove($rateAndReview);
        $this->manager->flush();
    }

    private function getOrderAndInventoryByProduct(Product $product, Customer $customer): ?array
    {
        /** @var \App\Entity\Order $order */
        $order = $this->orderRepository->findCustomerLatestDeliveredOrderByProduct($customer, $product);

        if ($order === null) {
            return null;
        }

        /** @var OrderItem $orderItem */
        $orderItem = $order
            ->getOrderItems()
            ->filter(fn (OrderItem $orderItem) => $orderItem->getInventory()->getVariant()->getProduct() === $product)
            ->first();

        return [$order, $orderItem->getInventory()];
    }
}
