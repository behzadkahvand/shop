<?php

namespace App\Service\ORM\CustomFilters\SellerPackage\Admin;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnCustomerSearchCustomFilter
 */
final class MultiColumnCustomerSearchCustomFilter implements CustomFilterInterface
{
    private EventDispatcherInterface $dispatcher;

    /**
     * MultiColumnCustomerSearchCustomFilter constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();
        $customer    = $queryParams['filter']['items.orderItems.orderItem.order.customer'] ?? null;

        if (null === $customer) {
            return;
        }

        if (is_array($customer)) {
            $customer = current($customer);
        }

        $fields = [
            'items.orderItems.orderItem.order.customer.name',
            'items.orderItems.orderItem.order.customer.family',
            'items.orderItems.orderItem.order.customer.nationalNumber',
            'items.orderItems.orderItem.order.customer.mobile',
            'items.orderItems.orderItem.order.customer.email',
            'items.orderItems.orderItem.order.customer',
        ];

        foreach ($fields as $field) {
            if (isset($queryParams['filter'][$field])) {
                unset($queryParams['filter'][$field]);
            }
        }

        $request->query->replace($queryParams);

        $listener = function (QueryBuilderFilterApplyingEvent $event) use (&$listener, $customer) {
            $this->dispatcher->removeListener(QueryBuilderFilterApplyingEvent::class, $listener);

            $queryBuilder = $event->getQueryBuilder();
            $rootAlias    = $event->getRootAlias();
            $context      = $event->getContext();

            $expression  = 'CONCAT(Customers.name, \' \', Customers.family) LIKE :customer_name OR ';
            $expression .= 'Customers.nationalNumber = :customer OR ';
            $expression .= 'Customers.mobile = :customer OR ';
            $expression .= 'Customers.email = :customer';

            $queryBuilder
                ->innerJoin("{$rootAlias}.items", 'SellerPackageItems')
                ->innerJoin('SellerPackageItems.orderItems', 'SellerOrderItems')
                ->innerJoin('SellerOrderItems.orderItem', 'OrderItems')
                ->innerJoin('OrderItems.order', 'Orders')
                ->innerJoin('Orders.customer', 'Customers')
                ->addSelect('SellerPackageItems', 'SellerOrderItems', 'OrderItems', 'Orders', 'Customers')
                ->andWhere($expression)
                ->setParameter('customer_name', "%$customer%")
                ->setParameter('customer', $customer);

            $context->setAlias(SellerPackage::class, SellerPackageItem::class, 'SellerPackageItems');
            $context->setAlias(SellerPackageItem::class, SellerOrderItem::class, 'SellerOrderItems');
            $context->setAlias(SellerOrderItem::class, OrderItem::class, 'OrderItems');
            $context->setAlias(OrderItem::class, Order::class, 'Orders');
            $context->setAlias(Order::class, Customer::class, 'Customers');
        };

        $this->dispatcher->addListener(QueryBuilderFilterApplyingEvent::class, $listener);
    }
}
