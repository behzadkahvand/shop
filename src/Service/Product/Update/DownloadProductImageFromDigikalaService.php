<?php

namespace App\Service\Product\Update;

use App\Entity\Media\ProductFeaturedImage;
use App\Entity\Media\ProductGallery;
use App\Entity\Product;
use App\Service\Media\UploadHelper;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DownloadProductImageFromDigikalaService
{
    public function __construct(
        protected UploadHelper $uploadHelper,
        protected Filesystem $filesystem,
        protected FilterManager $filterManager
    ) {
    }

    public function download(Product $product, string $sourceUrl, bool $shouldCoverWatermark, bool $isFeatureImage): void
    {
        $uploadedFile = $this->downloadImageIntoTempDir($sourceUrl);

        if ($shouldCoverWatermark) {
            $this->coverDigikalaWatermark($uploadedFile);
        }

        $uploadedPath = $this->uploadHelper->uploadImage($uploadedFile, $this->imageType($isFeatureImage));

        $this->addImageToProduct($product, $uploadedPath, $isFeatureImage);

        $this->removeTempFile($uploadedFile->getPathname());
    }

    protected function imageType(bool $isFeatureImage): string
    {
        return $isFeatureImage ? 'product-image' : 'product-gallery';
    }

    protected function coverDigikalaWatermark(UploadedFile $uploadedFile): void
    {
        $file = new FileBinary(
            $uploadedFile->getPathname(),
            $uploadedFile->getMimeType(),
            $uploadedFile->guessExtension()
        );

        $binary = $this->filterManager->applyFilter($file, 'digikala_watermark_cover');

        $this->filesystem->dumpFile($uploadedFile->getPathname(), $binary->getContent());
    }

    protected function downloadImageIntoTempDir(string $sourceUrl): UploadedFile
    {
        $fileName = bin2hex(random_bytes(10)) . '.jpg';
        $tempFile = sys_get_temp_dir() . '/' . $fileName;

        $digikalaImage = file_get_contents($sourceUrl);

        $this->filesystem->dumpFile($tempFile, $digikalaImage);

        return new UploadedFile($tempFile, $fileName);
    }

    protected function addImageToProduct(Product $product, string $path, bool $isFeatureImage): void
    {
        if ($isFeatureImage) {
            $this->addFeatureImageToProduct($product, $path);
        } else {
            $this->addGalleryImageToProduct($product, $path);
        }
    }

    protected function addFeatureImageToProduct(Product $product, string $path): void
    {
        $featureImage = new ProductFeaturedImage();
        $featureImage->setAlt($product->getTitle())->setPath($path);

        $product->setFeaturedImage($featureImage);
    }

    protected function addGalleryImageToProduct(Product $product, string $path): void
    {
        $galleryImage = new ProductGallery();
        $galleryImage->setAlt($product->getTitle())->setPath($path);

        $product->addImage($galleryImage);
    }

    protected function removeTempFile(string $tempFilePath): void
    {
        $this->filesystem->remove($tempFilePath);
    }
}
