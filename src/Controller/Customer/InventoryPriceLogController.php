<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Service\Product\Logs\InventoryPriceHistoryService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/price-chart", name: "price-chart.")]
class InventoryPriceLogController extends Controller
{
    public function __construct(private InventoryPriceHistoryService $inventoryPriceHistoryService)
    {
    }

    /**
     * @OA\Tag(name="inventoryPriceHistory")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *         filter[inventory_price.color.id]=10&filter[inventory_price.guarantee.id]=20.
     *         valid keys: inventory_price.product_id, inventory_price.variant_id, inventory_price.color.id, inventory_price.guarantee.id, inventory_price.active, inventory_price.month.max, inventory_price.month.min, inventory_price.seller_id, inventory_price.size.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of inventory price change history",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="priceFrom", type="integer"),
     *                 @OA\Property(property="finalPriceFrom", type="integer"),
     *                 @OA\Property(property="priceTo", type="integer"),
     *                 @OA\Property(property="finalPriceTo", type="integer"),
     *                 @OA\Property(property="sellerName", type="string"),
     *                 @OA\Property(property="guarantee", type="string"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="size", type="string"),
     *                 @OA\Property(property="date", type="string"),
     *            )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/filter-price-history", name: "filter-price-history", methods: ["GET"])]
    public function filterPriceHistory(Request $request): JsonResponse
    {
        $requestData         = $request->query->all();
        $requestData['sort'] = $requestData['sort'] ?? [];

        if (!isset($requestData['filter']['inventory_price.month.min'])) {
            $requestData['filter']['inventory_price.month.min'] = 3;
        }

        return $this->respond($this->inventoryPriceHistoryService->filterPriceHistory($requestData));
    }
}
