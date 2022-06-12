<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\FailPendingTransactionsCommand;
use App\Dictionary\TransactionStatus;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FailPendingTransactionsCommandTest extends MockeryTestCase
{
    private CommandTester $commandTester;

    private LegacyMockInterface|TransactionRepository|MockInterface|null $repository;

    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager    = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(TransactionRepository::class);

        $application = new Application();
        $application->add(new FailPendingTransactionsCommand(
            $this->manager,
            $this->repository
        ));

        $command             = $application->find('timcheh:job:fail-pending-transactions');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->manager    = null;
        $this->repository = null;

        Mockery::close();
    }

    public function testExecute(): void
    {
        $pendingTransactions = [new Transaction()];

        $this->repository->shouldReceive('findAllPendingTransactionsAfterSpecificTime')
                         ->once()
                         ->with(Mockery::type(DateTimeImmutable::class))
                         ->andReturn($pendingTransactions);

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->commandTester->execute([]);

        self::assertEquals(
            '[OK] You have successfully failed all pending orders!',
            trim($this->commandTester->getDisplay())
        );

        self::assertEquals(TransactionStatus::FAILED, $pendingTransactions[0]->getStatus());
    }
}
