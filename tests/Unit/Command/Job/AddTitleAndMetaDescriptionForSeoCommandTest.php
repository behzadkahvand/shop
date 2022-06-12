<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\AddTitleAndMetaDescriptionForSeoCommand;
use App\Repository\CategoryRepository;
use App\Service\Seo\AddTitleAndMetaDescriptionService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddTitleAndMetaDescriptionForSeoCommandTest extends BaseUnitTestCase
{
    private ?CommandTester $commandTester;
    private AddTitleAndMetaDescriptionService|Mockery\LegacyMockInterface|Mockery\MockInterface|null $addTitleAndMetaDescriptionServiceMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|CategoryRepository|null $categoryRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addTitleAndMetaDescriptionServiceMock = Mockery::mock(AddTitleAndMetaDescriptionService::class);
        $this->categoryRepositoryMock = Mockery::mock(CategoryRepository::class);

        $application = new Application();
        $application->add(new AddTitleAndMetaDescriptionForSeoCommand(
            $this->categoryRepositoryMock,
            $this->addTitleAndMetaDescriptionServiceMock
        ));

        $command = $application->find('timcheh:job:add-title-and-meta-description-for-seo');

        $this->commandTester = new CommandTester($command);
    }

    public function testItCanExecute(): void
    {
        $ids = [2, 5, 7, 8];

        $this->categoryRepositoryMock->expects('findAllIds')
            ->withNoArgs()
            ->andReturn($ids);

        $this->addTitleAndMetaDescriptionServiceMock->expects('handle')
            ->with($ids)
            ->andReturn();

        $this->commandTester->execute([]);
    }
}
