<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Product;
use App\Service\Layout\BlockAggregator;
use App\Service\Layout\OnSaleBlockAggregator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/layout", name: "layout.")]
class LayoutController extends Controller
{
    public function __construct(private BlockAggregator $block, private OnSaleBlockAggregator $onSaleBlockAggregator)
    {
    }

    /**
     * @OA\Tag(name="Layout")
     * @OA\Response(
     *      response=200,
     *      description="Return list of blocks",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="succeed", type="boolean"),
     *          @OA\Property(property="message", type="string"),
     *          @OA\Property(
     *              property="results",
     *              type="object",
     *              @OA\Property(
     *                  property="products",
     *                  type="array",
     *                  @OA\Items(ref=@Model(type=Product::class, groups={"product.search", "media"}))
     *              ),
     *              @OA\Property(
     *                  property="categories",
     *                  type="object",
     *                  @OA\Property(
     *                      property="categoryCode",
     *                      type="array",
     *                      @OA\Items(ref=@Model(type=Product::class, groups={"product.search", "media"}))
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="promotions",
     *                  type="object",
     *                  @OA\Property(
     *                      property="categoryCode",
     *                      type="array",
     *                      @OA\Items(ref=@Model(type=Product::class, groups={"product.search", "media"}))
     *                  ),
     *              ),
     *          ),
     *          @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *      )
     * )
     */
    #[Route("/blocks", name: "blocks", methods: ["GET"])]
    public function blocks(Request $request): JsonResponse
    {
        $context = array_merge($request->query->all(), [
            'serialization_groups' => ['product.search', 'media'],
        ]);

        return $this->respond(
            $this->block->generate($context),
            context: ['groups' => ['product.search', 'media']]
        );
    }

    /**
     * @OA\Tag(name="Layout")
     * @OA\Response(
     *      response=200,
     *      description="Return list of on sale blocks",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="succeed", type="boolean"),
     *          @OA\Property(property="message", type="string"),
     *          @OA\Property(
     *              property="results",
     *              type="object",
     *              @OA\Property(
     *                  property="onSaleInventories",
     *                  type="array",
     *                  @OA\Items(ref=@Model(type=Product::class, groups={"customer.layout.onSaleBlocks", "media"}))
     *              ),
     *              @OA\Property(
     *                  property="onSaleProducts",
     *                  type="array",
     *                  @OA\Items(ref=@Model(type=Product::class, groups={"customer.layout.onSaleBlocks", "media"}))
     *              ),
     *          ),
     *          @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *      )
     * )
     */
    #[Route("/on-sale-blocks", name: "on-sale-blocks", methods: ["GET"])]
    public function onSaleBlocks(): JsonResponse
    {
        $context = [
            'serialization_groups' => ['customer.layout.onSaleBlocks', 'media'],
        ];

        return $this->respond(
            $this->onSaleBlockAggregator->generate($context),
            context: ['groups' => ['customer.layout.onSaleBlocks', 'media']]
        );
    }
}
