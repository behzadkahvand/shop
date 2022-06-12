<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\AddBuyBoxToProductCommand;
use App\Repository\ProductRepository;
use App\Service\Product\BuyBox\AddBuyBoxService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddBuyBoxToProductCommandTest extends MockeryTestCase
{
    protected LegacyMockInterface|MockInterface|AddBuyBoxService|null $addBuyBoxMock;

    protected LegacyMockInterface|ProductRepository|MockInterface|null $productRepoMock;

    protected CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addBuyBoxMock   = Mockery::mock(AddBuyBoxService::class);
        $this->productRepoMock = Mockery::mock(ProductRepository::class);

        $application = new Application();
        $application->add(new AddBuyBoxToProductCommand(
            $this->addBuyBoxMock,
            $this->productRepoMock
        ));

        $command = $application->find('timcheh:job:add-buy-box-to-product');

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->addBuyBoxMock   = null;
        $this->productRepoMock = null;

        Mockery::close();
    }

    public function testItCanExecute()
    {
        $productIds = [2, 5, 7, 8];

        $this->productRepoMock->shouldReceive('getProductIdsHasInventory')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($productIds);

        $this->addBuyBoxMock->shouldReceive('addMany')
                            ->once()
                            ->with($productIds)
                            ->andReturn();

        $this->commandTester->execute([]);
    }
}
