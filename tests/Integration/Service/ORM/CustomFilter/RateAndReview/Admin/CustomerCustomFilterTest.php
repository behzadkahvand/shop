<?php

namespace App\Tests\Integration\Service\ORM\CustomFilter\RateAndReview\Admin;

use App\Service\ORM\CustomFilters\RateAndReview\Admin\CustomerCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerCustomFilterTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    private EventDispatcherInterface|LegacyMockInterface|MockInterface|null $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->dispatcher = Mockery::mock(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->dispatcher = null;
    }

    public function testShouldDoNothingWhenCustomerFilterIsNotSet(): void
    {
        $sut = new CustomerCustomFilter($this->dispatcher);
        $request      = new Request([
            'filter' => [
                'test'   => 'dummy value',
            ],
        ]);

        $sut->apply($request);

        self::assertEquals([
            'filter' => [
                'test'   => 'dummy value',

            ],
        ], $request->query->all());
    }

    public function testShouldApplyFilter(): void
    {
        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $sut = new CustomerCustomFilter($dispatcher);

        $customerName = 'john doe';
        $request      = new Request([
            'filter' => [
                'customer' => $customerName,
            ],
        ]);

        $sut->apply($request);

        self::assertEquals([
            'filter' => [],
        ], $request->query->all());

        $qb = new QueryBuilder(self::getContainer()->get(EntityManagerInterface::class));
        $qb->select('rate_and_review')->from('App\Entity\RateAndReview', 'rate_and_review');

        $event = new QueryBuilderFilterAppliedEvent(
            $qb,
            Mockery::mock(QueryContext::class),
            'rate_and_review',
            []
        );

        $dispatcher->dispatch($event);

        self::assertEquals(
            "SELECT rate_and_review FROM App\Entity\RateAndReview rate_and_review LEFT JOIN rate_and_review.customer Customer WHERE CONCAT(Customer.name, ' ', Customer.family) LIKE :customer_name",
            $qb->getQuery()->getDQL()
        );
        self::assertEquals(
            '%' . $customerName . '%',
            $qb->getQuery()->getParameter('customer_name')->getValue()
        );
    }
}
