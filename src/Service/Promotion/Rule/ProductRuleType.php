<?php

namespace App\Service\Promotion\Rule;

use App\Form\Promotion\RuleConfiguration\ProductFormType;
use App\Repository\ProductRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class ProductRuleType implements RuleTypeInterface
{
    public const CONFIGURATION_PRODUCT_IDS = 'product_ids';

    private ProductRepository $productRepository;

    private ContextOperationManager $contextOperationManager;

    public function __construct(ProductRepository $productRepository, ContextOperationManager $contextOperationManager)
    {
        $this->productRepository = $productRepository;
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool
    {
        $productIds = $configuration[self::CONFIGURATION_PRODUCT_IDS];

        // TODO use repository for better performance
        $subjectInventoryProductIdsMap = $this
            ->productRepository
            ->getProductIdsFromItemCollection($promotionSubject->getItems());

        $commonProductIds = array_intersect($productIds, array_keys($subjectInventoryProductIdsMap));
        $result = count($commonProductIds) > 0;

        if ($result) {
            $inventoryIds = [];
            foreach ($commonProductIds as $productId) {
                array_push($inventoryIds, ...$subjectInventoryProductIdsMap[$productId]);
            }

            if (!isset($context['inventory_ids'])) {
                $context['inventory_ids'] = $inventoryIds;
            } else {
                $context['inventory_ids'] = array_intersect($context['inventory_ids'], $inventoryIds);
            }
        } else {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'این کد تخفیف تنها بر روی کالاهای محدودی قابل استفاده است. '
            );
        }

        return $result;
    }

    public static function getName(): string
    {
        return 'product';
    }

    public function getConfigurationFormType(): string
    {
        return ProductFormType::class;
    }

    public static function getPriority(): int
    {
        return 10;
    }
}
