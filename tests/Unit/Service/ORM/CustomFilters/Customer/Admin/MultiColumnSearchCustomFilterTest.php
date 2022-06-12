<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Customer\Admin;

use App\Service\ORM\CustomFilters\Customer\Admin\MultiColumnSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class MultiColumnSearchCustomFilterTest extends FunctionalTestCase
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

    public function testItReturnIfCustomerFilterIsNotSet()
    {
        $customFilter = new MultiColumnSearchCustomFilter(\Mockery::mock(EventDispatcherInterface::class));
        $reqeust      = new Request();

        $customFilter->apply($reqeust);

        self::assertEquals([], $reqeust->query->all());
    }

    public function testItApplyCustomFilter()
    {
        $customer = 'customer';

        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $customFilter = new MultiColumnSearchCustomFilter($dispatcher);
        $reqeust      = new Request([
            'filter' => [
                'customer'       => ['like' => $customer],
                'name'           => 'customer name',
                'family'         => 'customer family',
                'nationalNumber' => '123456',
                'mobile'         => '09123456789',
                'email'          => 'customer@example.com',
            ],
        ]);

        $customFilter->apply($reqeust);

        self::assertEquals(['filter' => []], $reqeust->query->all());

        $expression = 'CONCAT(%1$s.name, \' \', %1$s.family) LIKE :customer_name OR ';
        $expression .= '%1$s.nationalNumber = :customer OR ';
        $expression .= '%1$s.mobile = :customer OR ';
        $expression .= '%1$s.email = :customer';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('setParameter')->once()->with('customer', $customer)->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')->once()->with('customer_name', "%{$customer}%")->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with(sprintf($expression, 'customer'))
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent($queryBuilder, \Mockery::mock(QueryContext::class), 'customer', []);

        $dispatcher->dispatch($event);
    }
}
