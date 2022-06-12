<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Messaging\Handlers\Command\Product\LogSearchHandler;
use App\Messaging\Messages\Command\Product\LogSearch;
use App\Service\Log\DataLoggerService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class LogSearchHandlerTest extends BaseUnitTestCase
{
    public function testItCanHandleLogSearchMessage(): void
    {
        $dataLoggerMock = Mockery::mock(DataLoggerService::class);
        $logSearchMock  = Mockery::mock(LogSearch::class);

        $sut = new LogSearchHandler($dataLoggerMock);

        $logSearchMock->expects('getTerm')
                      ->withNoArgs()
                      ->andReturns('test term');
        $logSearchMock->expects('getResultCount')
                      ->withNoArgs()
                      ->andReturns(4);
        $logSearchMock->expects('getCustomerId')
                      ->withNoArgs()
                      ->andReturns(7);

        $dataLoggerMock->expects('logProductSearch')
                       ->with('test term', 4, 7)
                       ->andReturns();

        ($sut)($logSearchMock);
    }
}
