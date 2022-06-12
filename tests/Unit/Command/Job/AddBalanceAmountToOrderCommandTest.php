<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\AddBalanceAmountToOrderCommand;
use App\Repository\OrderRepository;
use App\Service\Order\AddBalanceAmount\AddBalanceAmountService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddBalanceAmountToOrderCommandTest extends MockeryTestCase
{
    protected LegacyMockInterface|MockInterface|AddBalanceAmountService|null $addBalanceAmountMock;

    protected LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    protected CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addBalanceAmountMock = Mockery::mock(AddBalanceAmountService::class);
        $this->orderRepoMock        = Mockery::mock(OrderRepository::class);

        $application = new Application();
        $application->add(new AddBalanceAmountToOrderCommand(
            $this->addBalanceAmountMock,
            $this->orderRepoMock
        ));

        $command = $application->find('timcheh:job:add-balance-amount-to-order');

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->addBalanceAmountMock = null;
        $this->orderRepoMock        = null;

        Mockery::close();
    }

    public function testItCanExecute(): void
    {
        $orderIds = [2, 5, 7, 8];

        $this->orderRepoMock->shouldReceive('getOrderIdsForBalanceAmount')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($orderIds);

        $this->addBalanceAmountMock->shouldReceive('addMany')
                                   ->once()
                                   ->with($orderIds)
                                   ->andReturn();

        $this->commandTester->execute([]);
    }
}
