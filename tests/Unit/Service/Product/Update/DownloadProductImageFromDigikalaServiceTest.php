<?php

namespace App\Tests\Unit\Service\Product\Update;

use App\Entity\Product;
use App\Service\Media\UploadHelper;
use App\Tests\Unit\BaseUnitTestCase;
use App\Tests\Unit\TestDoubles\Stubs\DownloadProductFromDigikalaServiceStub;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DownloadProductImageFromDigikalaServiceTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|UploadHelper|null $uploadHelper;
    private Filesystem|LegacyMockInterface|MockInterface|null $fileSystem;
    private FilterManager|LegacyMockInterface|MockInterface|null $filterManager;
    private UploadedFile|LegacyMockInterface|MockInterface|null $uploadedFile;
    private DownloadProductFromDigikalaServiceStub|null $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->uploadHelper = Mockery::mock(UploadHelper::class);
        $this->fileSystem = Mockery::mock(Filesystem::class);
        $this->filterManager = Mockery::mock(FilterManager::class);
        $this->uploadedFile = Mockery::mock(UploadedFile::class);

        $this->sut = new DownloadProductFromDigikalaServiceStub(
            $this->uploadHelper,
            $this->fileSystem,
            $this->filterManager,
            $this->uploadedFile
        );
    }

    public function testForFeatureImage(): void
    {
        $uploadedPath = 'uploaded-path';
        $isFeatureImage = true;
        $imageType = 'product-image';
        $shouldCoverWatermark = false;
        $tmpPath = 'temp-path';

        $this->uploadHelper
            ->expects('uploadImage')
            ->with($this->uploadedFile, $imageType)
            ->andReturn($uploadedPath);

        $this->uploadedFile->expects('getPathName')->withNoArgs()->andReturn($tmpPath);

        $this->fileSystem->expects('remove')->with($tmpPath)->andReturnNull();

        $product = new Product();
        $product->setTitle('dummy product');

        $this->sut->download($product, 'source-url', $shouldCoverWatermark, $isFeatureImage);

        $image = $product->getFeaturedImage();
        self::assertEquals($product->getTitle(), $image->getAlt());
        self::assertEquals($uploadedPath, $image->getPath());
    }

    public function testForGalleryImage(): void
    {
        $uploadedPath = 'uploaded-path';
        $isFeatureImage = false;
        $imageType = 'product-gallery';
        $shouldCoverWatermark = false;
        $tmpPath = 'temp-path';

        $this->uploadHelper
            ->expects('uploadImage')
            ->with($this->uploadedFile, $imageType)
            ->andReturn($uploadedPath);

        $this->uploadedFile->expects('getPathName')->withNoArgs()->andReturn($tmpPath);

        $this->fileSystem->expects('remove')->with($tmpPath)->andReturnNull();

        $product = new Product();
        $product->setTitle('dummy product');

        $this->sut->download($product, 'source-url', $shouldCoverWatermark, $isFeatureImage);

        $images = $product->getImages();
        self::assertCount(1, $images);
        self::assertEquals($product->getTitle(), $images[0]->getAlt());
        self::assertEquals($uploadedPath, $images[0]->getPath());
    }

    public function testWhenCoverWatermarkIsTrue(): void
    {
        $tmpPath = 'temp-path';
        $uploadedPath = 'uploaded-path';
        $isFeatureImage = false;
        $imageType = 'product-gallery';
        $shouldCoverWatermark = true;

        $this->uploadedFile->expects('getPathName')->times(3)->withNoArgs()->andReturn($tmpPath);
        $this->uploadedFile->expects('getMimeType')->once()->withNoArgs()->andReturn('mime');
        $this->uploadedFile->expects('guessExtension')->once()->withNoArgs()->andReturn('ext');

        $binaryFile = Mockery::mock(BinaryInterface::class);
        $binaryContent = '11001101010';

        $this->filterManager
            ->expects('applyFilter')
            ->with(Mockery::type(FileBinary::class), 'digikala_watermark_cover')
            ->andReturn($binaryFile);

        $binaryFile->expects('getContent')->withNoArgs()->andReturn($binaryContent);

        $this->fileSystem->expects('dumpFile')->with($tmpPath, $binaryContent)->andReturnNull();
        $this->fileSystem->expects('remove')->with($tmpPath)->andReturnNull();

        $this->uploadHelper
            ->expects('uploadImage')
            ->with($this->uploadedFile, $imageType)
            ->andReturn($uploadedPath);

        $product = new Product();
        $product->setTitle('dummy product');

        $this->sut->download($product, 'source-url', $shouldCoverWatermark, $isFeatureImage);

        $images = $product->getImages();
        self::assertCount(1, $images);
        self::assertEquals($product->getTitle(), $images[0]->getAlt());
        self::assertEquals($uploadedPath, $images[0]->getPath());
    }
}
