<?php

namespace App\Tests\Unit\Command\Seller;

use App\Command\Seller\UpdateSellerScoreCommand;
use App\Service\Seller\SellerScore\SellerScoreBatchUpdateService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateSellerScoreCommandTest extends BaseUnitTestCase
{
    public function testShouldWorkCorrectly(): void
    {
        $path = 'dummy/path';
        $sellerScoreBatchUpdateService = Mockery::mock(SellerScoreBatchUpdateService::class);

        $application = new Application();
        $application->add(new UpdateSellerScoreCommand($sellerScoreBatchUpdateService));

        $command = $application->find('timcheh:update-seller-score');
        $commandTester = new CommandTester($command);

        $sellerScoreBatchUpdateService->shouldReceive('execute')->once()->with($path)->andReturnNull();

        $commandTester->execute(['path' => $path]);
    }
}
