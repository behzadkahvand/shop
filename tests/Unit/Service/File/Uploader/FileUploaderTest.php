<?php

namespace App\Tests\Unit\Service\File\Uploader;

use App\Service\File\Uploader\FileUploader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderTest extends MockeryTestCase
{
    public function testItCanUploadFileSuccessfully(): void
    {
        $fileName = 'test.csv';
        $publicDirectory = 'public';

        $file = m::mock(UploadedFile::class);
        $file
            ->shouldReceive('getClientOriginalName')
            ->once()
            ->withNoArgs()
            ->andReturn($fileName);

        $slugger = m::mock(SluggerInterface::class);
        $slugger
            ->shouldReceive('slug')
            ->once();

        $file
            ->shouldReceive('getClientOriginalExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('csv');

        $file
            ->shouldReceive('move')
            ->once();

        $fileUploader = new FileUploader($publicDirectory, $slugger);

        $fileUploader->upload($file);
    }
}
