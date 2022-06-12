<?php

namespace App\Tests\Unit\Service\Product\Logs;

use App\Messaging\Messages\Command\Product\LogSearch;
use App\Service\Product\Logs\SearchLogService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SearchLogServiceTest extends BaseUnitTestCase
{
    protected ?MessageBusInterface $messageBusMock;

    protected ?SearchLogService $searchLogService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->searchLogService = new SearchLogService($this->messageBusMock);
    }

    /**
     * @dataProvider dispatchSearchLogMsgData
     */
    public function testCanCallDispatchSearchLogMsg(string $term, int $resultCount, ?int $customerId): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(LogSearch::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->searchLogService->dispatchSearchLogMsg($term, $resultCount, $customerId);
    }

    public function dispatchSearchLogMsgData(): array
    {
        return [
            ['samsung', 100, null],
            ['test', 0, null],
            ['test', 0, 20],
            ['samsung', 250, 20],
        ];
    }
}
