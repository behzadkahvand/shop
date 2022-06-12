<?php

namespace App\Tests\Unit\Command\Notification;

use App\Command\Notification\ImportNotificationsCommand;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ImportNotificationsCommandTest extends MockeryTestCase
{
    private ?CommandTester $commandTester;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $manager;

    private ?Application $application;


    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = Mockery::mock(EntityManagerInterface::class);

        $this->application = new Application();
        $this->application->add(new ImportNotificationsCommand($this->manager));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->manager     = null;
        $this->application = null;

        Mockery::close();
    }

    public function testExecuteWhenNewCommandAddedSuccessfully(): void
    {
        $command             = $this->application->find('timcheh:import-notifications');
        $this->commandTester = new CommandTester($command);

        $this->manager->expects('persist')
                      ->with(Mockery::type(Notification::class))
                      ->andReturn();

        $this->manager->expects('flush')
                      ->withNoArgs()
                      ->andReturn();

        $this->commandTester->execute([
            "--command_dto_namespace" => "App\Service\Notification\DTOs\Customer\Campaign\BlackFriday\BlackFridayLandingSmsNotificationDto",
        ]);

        self::assertEquals(
            '[OK] You have successfully imported notifications!',
            trim($this->commandTester->getDisplay())
        );
    }

    public function testExecuteWhenNewCommandAddFailed(): void
    {
        $command             = $this->application->find('timcheh:import-notifications');
        $this->commandTester = new CommandTester($command);

        $this->expectException(Exception::class);

        $this->commandTester->execute([
            "--command_dto_namespace" => "App\Service\Notification\DTOs\Customer\wrongNamespace",
        ]);
    }

    public function testExecuteImportedAllCommandSuccessfully(): void
    {
        $command             = $this->application->find('timcheh:import-notifications');
        $this->commandTester = new CommandTester($command);

        $this->manager->expects('persist')
                      ->times(28)
                      ->with(Mockery::type(Notification::class))
                      ->andReturn();

        $this->manager->expects('flush')
                      ->withNoArgs()
                      ->andReturn();

        $this->commandTester->execute([]);

        self::assertEquals(
            '[OK] You have successfully imported notifications!',
            trim($this->commandTester->getDisplay())
        );
    }
}
