<?php

namespace App\Service\Product\Import\Digikala;

use App\Dictionary\ProductImportStatusDictionary;
use App\Document\ProductImportLog;
use App\Entity\Product;
use App\Entity\Seller;
use App\Exceptions\Product\Import\DuplicateProductException;
use App\Exceptions\Product\Import\ProductImportException;
use App\Messaging\Messages\Command\Product\ImportProductImagesFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\builder\ProductBuilder;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\PropertyUpdaterFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportDigikalaProductService
{
    public function __construct(
        protected ProductBuilder $productBuilder,
        protected OutsourceProductUpdateService $outsourceProductUpdateService,
        protected ProductRepository $productRepository,
        protected EntityManagerInterface $em,
        protected MessageBusInterface $bus,
        protected PropertyUpdaterFactory $updaterFactory,
        protected DocumentManager $documentManager
    ) {
    }

    /**
     * @throws DuplicateProductException
     * @throws ProductImportException
     */
    public function import(string $digikalaDkp, ?Seller $seller = null, ?string $digikalaSellerId = null): void
    {
        $log = $this->createImportLog($digikalaDkp, $seller, $digikalaSellerId);

        try {
            $product = $this->productBuilder
                ->withDigikalaDkp($digikalaDkp)
                ->withSeller($seller)
                ->build();

            $this->outsourceProductUpdateService->update(
                $product,
                $this->updaterFactory->makeTitleUpdater(),
                $this->updaterFactory->makeBrandUpdater(),
                $this->updaterFactory->makeCategoryUpdater(),
                $this->updaterFactory->makeSpecificationsUpdater()
            );

            $this->validateIsUnique($product);
        } catch (Exception $e) {
            $log->fail($e->getMessage());
            $this->documentManager->flush();

            throw $e;
        }

        $this->em->persist($product);
        $this->em->flush();

        $log->success();
        $this->documentManager->flush();

        $this->bus->dispatch(new ImportProductImagesFromDigikala($product->getId()));
    }

    /**
     * @throws DuplicateProductException
     */
    private function validateIsUnique(Product $product): void
    {
        $result = $this->productRepository->findByTitleOrDigikalaDkp(
            $product->getTitle(),
            $product->getDigikalaDkp()
        );

        if (isset($result)) {
            throw new DuplicateProductException();
        }
    }

    private function createImportLog(string $digikalaDkp, ?Seller $seller, ?string $digikalaSellerId): ProductImportLog
    {
        $log = new ProductImportLog();
        $log->setDkProductId($digikalaDkp)
            ->setSellerId($seller->getId())
            ->setDkSellerId($digikalaSellerId)
            ->setStatus(ProductImportStatusDictionary::PENDING);

        $this->documentManager->persist($log);
        $this->documentManager->flush();

        return $log;
    }
}
