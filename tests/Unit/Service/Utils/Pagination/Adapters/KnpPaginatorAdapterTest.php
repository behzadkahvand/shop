<?php

namespace App\Tests\Unit\Service\Utils\Pagination\Adapters;

use App\Service\Utils\Pagination\Adapters\KnpPaginatorAdapter;
use App\Service\Utils\Pagination\Pagination;
use Knp\Component\Pager\PaginatorInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;

class KnpPaginatorAdapterTest extends MockeryTestCase
{
    public function testItCanPaginate()
    {
        $knpPaginatorMock = Mockery::mock(PaginatorInterface::class);
        $paginatorAdapter = new KnpPaginatorAdapter($knpPaginatorMock);

        $knpPaginatorMock->shouldReceive('paginate')
                         ->once()
                         ->with(
                             range(1, 10),
                             1,
                             20,
                             [
                                 'wrap-queries' => true,
                             ]
                         )
                         ->andReturn();

        $paginatorAdapter->paginate(range(1, 10), new Pagination());
    }
}
