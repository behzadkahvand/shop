<?php

namespace App\Tests\Unit\TestDoubles\Stubs;

use App\Service\Media\UploadHelper;
use App\Service\Product\Update\DownloadProductImageFromDigikalaService;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DownloadProductFromDigikalaServiceStub extends DownloadProductImageFromDigikalaService
{
    public function __construct(
        UploadHelper $uploadHelper,
        Filesystem $filesystem,
        FilterManager $filterManager,
        protected UploadedFile $uploadedFile
    ) {
        parent::__construct($uploadHelper, $filesystem, $filterManager);
    }

    protected function downloadImageIntoTempDir(string $sourceUrl): UploadedFile
    {
        return $this->uploadedFile;
    }
}
