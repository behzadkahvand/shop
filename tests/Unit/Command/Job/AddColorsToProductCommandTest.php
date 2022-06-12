<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\AddColorsToProductsCommand;
use App\Repository\ProductRepository;
use App\Service\Product\Colors\AddColorsService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddColorsToProductCommandTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|ProductRepository|Mockery\MockInterface|null $productRepositoryMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|AddColorsService|null $addColorsServiceMock;
    private ?CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addColorsServiceMock = Mockery::mock(AddColorsService::class);
        $this->productRepositoryMock = Mockery::mock(ProductRepository::class);

        $application = new Application();
        $application->add(new AddColorsToProductsCommand(
            $this->productRepositoryMock,
            $this->addColorsServiceMock
        ));

        $command = $application->find('timcheh:job:add-colors-to-product');
        $this->commandTester = new CommandTester($command);
    }

    public function testItCanExecute(): void
    {
        $productIds = [1, 2, 3];

        $this->productRepositoryMock->expects('getAvailableProductIds')
            ->withNoArgs()
            ->andReturn($productIds);

        $this->addColorsServiceMock->expects('batchAdd')
            ->with($productIds)
            ->andReturn();

        $this->commandTester->execute([]);
    }

    public function testItDoNothingWhenThereIsNoProduct(): void
    {
        $this->productRepositoryMock->expects('getAvailableProductIds')
            ->withNoArgs()
            ->andReturn([]);

        $this->commandTester->execute([]);
    }
}
