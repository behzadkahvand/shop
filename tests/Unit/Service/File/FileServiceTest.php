<?php

namespace App\Tests\Unit\Service\File;

use App\Dictionary\FileHandlerPresenterModels;
use App\Service\File\FileHandlerFactory;
use App\Service\File\FileHandlerInterface;
use App\Service\File\FileService;
use App\Service\File\RowAbstract;
use Iterator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FileServiceTest extends MockeryTestCase
{
    public function testItCanOpenFileSuccessfully(): void
    {
        $filePath = 'temp';
        $presenterModel = FileHandlerPresenterModels::TRACKING_CODE;

        $fileHandlerFactory = m::mock(FileHandlerFactory::class);
        $fileHandlerFactory
            ->shouldReceive('create')
            ->once()
            ->with($filePath, $presenterModel)
            ->andReturn(m::mock(FileHandlerInterface::class));

        $fileService = new FileService($fileHandlerFactory);

        $fileService->create($filePath, $presenterModel);
    }

    public function testItCanReturnFileRowsAsCollectionSuccessfully(): void
    {
        $filePath = 'temp';
        $presenterModel = FileHandlerPresenterModels::TRACKING_CODE;

        $fileHandler = m::mock(FileHandlerInterface::class);

        $fileHandlerFactory = m::mock(FileHandlerFactory::class);
        $fileHandlerFactory
            ->shouldReceive('create')
            ->once()
            ->with($filePath, $presenterModel)
            ->andReturn($fileHandler);

        $rowIterator = m::mock(Iterator::class);

        $fileHandler
            ->shouldReceive('read')
            ->with($filePath)
            ->andReturn($rowIterator);

        $rowIterator
            ->shouldReceive('valid')
            ->withNoArgs()
            ->andReturnTrue();

        $rowIterator
            ->shouldReceive('current')
            ->withNoArgs()
            ->andReturn(m::mock(RowAbstract::class));

        $rowIterator
            ->shouldReceive('next')
            ->withNoArgs()
            ->andReturn();

        $fileHandler
            ->shouldReceive('close')
            ->withNoArgs()
            ->andReturn();

        $fileService = new FileService($fileHandlerFactory);

        $fileService->create($filePath, $presenterModel)->getRows();
    }
}
