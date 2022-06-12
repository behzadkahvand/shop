<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Customer\Admin;

use App\Service\ORM\CustomFilters\Customer\Admin\IsLegalSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IsLegalSearchCustomFilterTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->dispatcherMock = Mockery::mock(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testItDoNothingWhenIsLegalFilterIsNotSet(): void
    {
        $customFilter = new IsLegalSearchCustomFilter($this->dispatcherMock);
        $request      = new Request([
            'filter' => [
                'name'   => 'customer name',
                'family' => 'customer family',
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals([
            'filter' => [
                'name'   => 'customer name',
                'family' => 'customer family',
            ],
        ], $request->query->all());
    }

    public function testItCanApplyIsLegalFilter(): void
    {
        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $customFilter = new IsLegalSearchCustomFilter($dispatcher);
        $request      = new Request([
            'filter' => [
                'isLegal' => 1,
                'name'    => 'customer name',
                'family'  => 'customer family',
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals([
            'filter' => [
                'name'    => 'customer name',
                'family'  => 'customer family',
            ],
        ], $request->query->all());

        $queryBuilder = Mockery::mock(QueryBuilder::class);

        $queryBuilder->shouldReceive('leftJoin')
                     ->once()
                     ->with("customer.legalAccount", 'LegalAccount')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('LegalAccount.id > 0')
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent(
            $queryBuilder,
            Mockery::mock(QueryContext::class),
            'customer',
            []
        );

        $dispatcher->dispatch($event);
    }

    public function testItCanApplyIsNotLegalFilter(): void
    {
        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $customFilter = new IsLegalSearchCustomFilter($dispatcher);
        $request      = new Request([
            'filter' => [
                'isLegal' => 0,
                'name'    => 'customer name',
                'family'  => 'customer family',
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals([
            'filter' => [
                'name'    => 'customer name',
                'family'  => 'customer family',
            ],
        ], $request->query->all());

        $queryBuilder = Mockery::mock(QueryBuilder::class);

        $queryBuilder->shouldReceive('leftJoin')
                     ->once()
                     ->with("customer.legalAccount", 'LegalAccount')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('LegalAccount.id IS NULL')
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent(
            $queryBuilder,
            Mockery::mock(QueryContext::class),
            'customer',
            []
        );

        $dispatcher->dispatch($event);
    }
}
