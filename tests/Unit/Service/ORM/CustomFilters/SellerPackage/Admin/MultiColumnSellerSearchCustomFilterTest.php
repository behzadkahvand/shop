<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\SellerPackage\Admin;

use App\Entity\Seller;
use App\Entity\SellerPackage;
use App\Service\ORM\CustomFilters\SellerPackage\Admin\MultiColumnSellerSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnSellerSearchCustomFilterTest
 */
final class MultiColumnSellerSearchCustomFilterTest extends FunctionalTestCase
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
        $customFilter = new MultiColumnSellerSearchCustomFilter(\Mockery::mock(EventDispatcherInterface::class));
        $reqeust      = new Request();

        $customFilter->apply($reqeust);

        self::assertEquals([], $reqeust->query->all());
    }

    public function testItApplyCustomFilter()
    {
        $seller = 'seller';

        $dispatcher   = self::$container->get(EventDispatcherInterface::class);
        $customFilter = new MultiColumnSellerSearchCustomFilter($dispatcher);
        $reqeust      = new Request([
            'filter' => [
                'seller' => ['like' => $seller],
            ],
        ]);

        $customFilter->apply($reqeust);

        self::assertEquals(['filter' => []], $reqeust->query->all());

        $expression = 'Sellers.identifier = :seller OR ';
        $expression .= 'Sellers.nationalNumber = :seller OR ';
        $expression .= 'Sellers.nationalIdentifier = :seller OR ';
        $expression .= 'Sellers.name like :seller_name';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('innerJoin')
                     ->once()
                     ->with('sellerPackage.seller', 'Sellers')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('addSelect')
                     ->once()
                     ->with('Sellers')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with(sprintf($expression, 'seller'))
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')
                     ->once()
                     ->with('seller_name', "%{$seller}%")
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')->once()->with('seller', $seller)->andReturnSelf();

        $queryContext = \Mockery::mock(QueryContext::class);
        $queryContext->shouldReceive('setAlias')
                     ->once()
                     ->with(SellerPackage::class, Seller::class, 'Sellers')
                     ->andReturn();
        $event = new QueryBuilderFilterApplyingEvent($queryBuilder, $queryContext, 'sellerPackage');

        $dispatcher->dispatch($event);
    }
}
