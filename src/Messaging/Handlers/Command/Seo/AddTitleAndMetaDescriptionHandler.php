<?php

namespace App\Messaging\Handlers\Command\Seo;

use App\Entity\Category;
use App\Messaging\Messages\Command\Seo\AddTitleAndMetaDescription;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Service\Seo\SeoSelectedFilter\UpdateOrCreateSeoSelectedFiltersService;
use App\Service\Utils\GenerateSoeMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddTitleAndMetaDescriptionHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private GenerateSoeMetadata $generateSoeMetadata,
        private UpdateOrCreateSeoSelectedFiltersService $createSeoSelectedFiltersService
    ) {
    }

    public function __invoke(AddTitleAndMetaDescription $addTitleAndMetaDescription): void
    {
        $categoryId = $addTitleAndMetaDescription->getCategoryId();
        $this->entityManager->beginTransaction();
        try {
            $category = $this->entityManager->getReference(Category::class, $categoryId);
            if (!$category) {
                $this->logger->error(sprintf('Category %d is not exists for update pageTitle or metaDescription', $categoryId));

                return;
            }

            $categoryLeafIds = $this->categoryRepository->getCategoryLeafIdsForCategory($category);
            $categoryLeafIds = explode(',', $categoryLeafIds);
            $brands = $this->brandRepository->getBrandsForProductSearch($categoryLeafIds);

            $this->updateCategory($category);
            $this->createSeoSelectedFiltersService->updateOrCreate($category, $brands);

            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->entityManager->close();
            $this->entityManager->rollBack();

            throw $exception;
        }
    }

    private function updateCategory(Category $category): void
    {
        $update = 0;
        $categoryName = $category->getTitle();
        if (!$category->getPageTitle()) {
            $category->setPageTitle($this->generateSoeMetadata->title($categoryName));
            $update = 1;
        }

        if (!$category->getMetaDescription()) {
            $category->setMetaDescription($this->generateSoeMetadata->metaDescription($categoryName));
            $update = 1;
        }

        if ($update) {
            $this->entityManager->flush();
        }
    }
}
