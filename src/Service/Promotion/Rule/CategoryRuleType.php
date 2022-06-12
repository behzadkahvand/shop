<?php

namespace App\Service\Promotion\Rule;

use App\Entity\Promotion;
use App\Form\Promotion\RuleConfiguration\CategoryFormType;
use App\Form\Promotion\RuleConfiguration\MaximumOrdersCountFormType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class CategoryRuleType implements RuleTypeInterface
{
    public const CONFIGURATION_CATEGORY_IDS = 'category_ids';

    private CategoryRepository $categoryRepository;

    private ContextOperationManager $contextOperationManager;

    public function __construct(
        CategoryRepository $categoryRepository,
        ContextOperationManager $contextOperationManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool
    {
        $categoryIds = $configuration[self::CONFIGURATION_CATEGORY_IDS];
        $leaves = $this->categoryRepository->getCategoryLeafIdsForCategoryIds(...$categoryIds);

        $subjectInventoryCategoryIdsMap = $this
            ->categoryRepository
            ->getCategoryIdsFromItemCollection($promotionSubject->getItems());

        $commonCategoryIds = array_intersect($leaves, array_keys($subjectInventoryCategoryIdsMap));

        $result = count($commonCategoryIds) > 0;

        if ($result) {
            $inventoryIds = [];
            foreach ($commonCategoryIds as $categoryId) {
                array_push($inventoryIds, ...$subjectInventoryCategoryIdsMap[$categoryId]);
            }

            if (!isset($context['inventory_ids'])) {
                $context['inventory_ids'] = $inventoryIds;
            } else {
                $context['inventory_ids'] = array_intersect($context['inventory_ids'], $inventoryIds);
            }
        } else {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'این کد تخفیف تنها بر روی دسته بندی های محدودی قابل استفاده است. '
            );
        }

        return $result;
    }

    public static function getName(): string
    {
        return 'category';
    }

    public function getConfigurationFormType(): string
    {
        return CategoryFormType::class;
    }

    public static function getPriority(): int
    {
        return 10;
    }
}
