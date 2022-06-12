<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\SellerPackage\Admin;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\ORM\CustomFilters\SellerPackage\Admin\MultiColumnCustomerSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnCustomerSearchCustomFilterTest
 */
final class MultiColumnCustomerSearchCustomFilterTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    public function testItReturnIfSellerFilterIsNotSet()
    {
        $customFilter = new MultiColumnCustomerSearchCustomFilter(\Mockery::mock(EventDispatcherInterface::class));
        $reqeust      = new Request();

        $customFilter->apply($reqeust);

        self::assertEquals([], $reqeust->query->all());
    }

    public function testItApplyCustomFilter()
    {
        $customer = 'seller';

        $dispatcher   = self::$container->get(EventDispatcherInterface::class);
        $customFilter = new MultiColumnCustomerSearchCustomFilter($dispatcher);
        $reqeust      = new Request([
            'filter' => [
                'items.orderItems.orderItem.order.customer' => ['like' => $customer],
            ],
        ]);

        $customFilter->apply($reqeust);

        self::assertEquals(['filter' => []], $reqeust->query->all());

        $expression = 'CONCAT(Customers.name, \' \', Customers.family) LIKE :customer_name OR ';
        $expression .= 'Customers.nationalNumber = :customer OR ';
        $expression .= 'Customers.mobile = :customer OR ';
        $expression .= 'Customers.email = :customer';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('sellerPackage.items', 'SellerPackageItems')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('SellerPackageItems.orderItems', 'SellerOrderItems')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('SellerOrderItems.orderItem', 'OrderItems')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('OrderItems.order', 'Orders')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('Orders.customer', 'Customers')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('addSelect')
                     ->once()
                     ->with('SellerPackageItems', 'SellerOrderItems', 'OrderItems', 'Orders', 'Customers')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with(sprintf($expression, 'seller'))
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')
                     ->once()
                     ->with('customer_name', "%{$customer}%")
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')->once()->with('customer', $customer)->andReturnSelf();

        $queryContext = \Mockery::mock(QueryContext::class);
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(SellerPackage::class, SellerPackageItem::class, 'SellerPackageItems')
                     ->andReturn();
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(SellerPackageItem::class, SellerOrderItem::class, 'SellerOrderItems')
                     ->andReturn();
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(SellerOrderItem::class, OrderItem::class, 'OrderItems')
                     ->andReturn();
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(OrderItem::class, Order::class, 'Orders')
                     ->andReturn();
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(Order::class, Customer::class, 'Customers')
                     ->andReturn();

        $event = new QueryBuilderFilterApplyingEvent($queryBuilder, $queryContext, 'sellerPackage');

        $dispatcher->dispatch($event);
    }
}
