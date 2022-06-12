<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Product\Customer;

use App\Service\ORM\CustomFilters\Product\Customer\TitleSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class TitleSearchCustomFilterTest
 */
final class TitleSearchCustomFilterTest extends FunctionalTestCase
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

    public function testItReturnIfTitleFilterIsNotSet()
    {
        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldNotReceive('addListener');

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $customFilter = new TitleSearchCustomFilter($dispatcher, $websiteAreaService);

        $request = new Request();
        $customFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    /**
     * @dataProvider sellerAreaTitleProvider
     */
    public function testItDoesNotReturnIfWebsiteAreaIsSellerAndTitleFormatIsCurrect(string $title): void
    {
        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldNotReceive('addListener');

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->withNoArgs()->andReturnTrue();

        $customFilter = new TitleSearchCustomFilter($dispatcher, $websiteAreaService);

        $request = new Request(['filter' => ['title' => $title]]);
        $customFilter->apply($request);

        self::assertEquals(['filter' => ['title' => $title]], $request->query->all());
    }

    public function sellerAreaTitleProvider(): iterable
    {
        yield ['1234'];
        yield ['tpi-1234'];
    }

    /**
     * @dataProvider titleFilterProvider
     */
    public function testItApplyTitleFilter($filter, string $title)
    {
        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnFalse();

        $customFilter = new TitleSearchCustomFilter($dispatcher, $websiteAreaService);

        $request = new Request(['filter' => ['title' => $filter]]);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['title' => $filter]], $request->query->all());

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('product.title LIKE :product_title OR product.subtitle LIKE :product_title OR product.additionalTitle LIKE :product_title')
                     ->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')
                     ->once()
                     ->with('product_title', "%{$title}%")
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent(
            $queryBuilder,
            \Mockery::mock(QueryContext::class),
            'product',
            []
        );

        $dispatcher->dispatch($event);
    }

//    public function testItApplyTitleFilterWithSentenceString()
//    {
//        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
//
//        $customFilter = new TitleSearchCustomFilter($dispatcher);
//
//        $title = ' product title chunk by items ';
//
//        $chunkTitles = explode(' ', trim($title));
//
//        $chunkTitles = array_values(array_filter($chunkTitles, fn($chunk) => mb_strlen($chunk) >= 3));
//
//        $request = new Request(['filter' => ['title' => $title]]);
//
//        $customFilter->apply($request);
//
//        self::assertEquals(['filter' => ['title' => $title]], $request->query->all());
//
//        $queryBuilder = \Mockery::mock(QueryBuilder::class);
//
//        $query = 'product.title LIKE :product_title OR product.subtitle LIKE :product_title OR product.additionalTitle LIKE :product_title';
//
//        $queryBuilder->shouldReceive('setParameter')
//                     ->once()
//                     ->with('product_title', "%{$title}%")
//                     ->andReturnSelf();
//
//        foreach ($chunkTitles as $key => $chunkTitle) {
//            $key = 'product_title' . ($key + 1);
//            $queryBuilder->shouldReceive('setParameter')
//                         ->once()
//                         ->with($key, "%{$chunkTitle}%")
//                         ->andReturnSelf();
//            $query .= ' OR ' . sprintf('product.title LIKE :%1$s OR product.subtitle LIKE :%1$s OR product.additionalTitle LIKE :%1$s', $key);
//        }
//
//        $queryBuilder->shouldReceive('andWhere')
//                     ->once()
//                     ->with($query)
//                     ->andReturnSelf();
//
//        $event = new QueryBuilderFilterAppliedEvent(
//            $queryBuilder,
//            \Mockery::mock(QueryContext::class),
//            'product',
//            []
//        );
//
//        $dispatcher->dispatch($event);
//    }

    public function titleFilterProvider()
    {
        return [
            ['foobar', 'foobar'],
            [['like' => 'foobar'], 'foobar'],
        ];
    }
}
