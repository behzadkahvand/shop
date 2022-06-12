<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ProductVariant;
use App\Form\Type\Admin\ProductVariantAndInventoryType;
use App\Form\Type\ProductVariantType;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\ProductVariant\CreateProductVariantWithInventoryService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/product/variants", name: "product_variants.")]
class ProductVariantController extends Controller
{
    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[product.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=product.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of product variants",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductVariant::class, groups={"variant.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(
        Request $request,
        QueryBuilderFilterService $filterService
    ): JsonResponse {
        return $this->respondWithPagination(
            $filterService->filter(ProductVariant::class, $request->query->all()),
            context: ['groups' => ['variant.index']]
        );
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="product", type="integer", description="Product id"),
     *         @OA\Property(
     *             property="optionValues",
     *             type="array",
     *             @OA\Items(type="integer", description="Product option id")
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Created product variant",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductVariant::class, groups={"variant.create"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Failed validation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Validation error has been detected!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            ProductVariantType::class,
            options: [
                'validation_groups' => ['create', 'variant.create'], 'method' => $request->getMethod(),
            ]
        )->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $variant = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($variant);
            $entityManager->flush();

            return $this->respond($variant, context: ['groups' => 'variant.create']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="code", type="string"),
     *         @OA\Property(property="product", type="integer", description="Product id"),
     *         @OA\Property(
     *             property="optionValues",
     *             type="array",
     *             @OA\Items(type="integer", description="Product option value id")
     *         ),
     *         @OA\Property(property="seller", type="integer", description="Seller id"),
     *         @OA\Property(property="stock", type="integer"),
     *         @OA\Property(property="price", type="integer"),
     *         @OA\Property(property="finalPrice", type="integer"),
     *         @OA\Property(property="maxPurchasePerOrder", type="integer"),
     *         @OA\Property(property="suppliesIn", type="integer")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Create product variant with inventory",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductVariant::class, groups={"variant.create"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Failed validation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Validation error has been detected!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/create-inventory", name: "store_inventory", methods: ["POST"])]
    public function storeVariantInventory(
        Request $request,
        CreateProductVariantWithInventoryService $createProductVariantWithInventory
    ): JsonResponse {
        $form = $this->createForm(ProductVariantAndInventoryType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $productVariantData = $form->getData();

        $variant = $createProductVariantWithInventory->perform($productVariantData);

        return $this->respond($variant, context: ['groups' => 'variant.create']);
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Response(
     *     response=200,
     *     description="Return a product variant",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductVariant::class, groups={"variant.show", "media"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ProductVariant $variant): JsonResponse
    {
        return $this->respond($variant, context: ['groups' => ['variant.show', 'media']]);
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="product", type="integer", description="Product id"),
     *         @OA\Property(
     *             property="optionValues",
     *             type="array",
     *             @OA\Items(type="integer", description="Product option id")
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated product variant",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductVariant::class, groups={"variant.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Failed validation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Validation error has been detected!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT", "PATCH"])]
    public function update(Request $request, ProductVariant $variant): JsonResponse
    {
        $form = $this->createForm(
            ProductVariantType::class,
            $variant,
            [
                'validation_groups' => ['update', 'variant.update'], 'method' => $request->getMethod(),
            ]
        )->submit($request->request->all(), 'PATCH' !== $request->getMethod());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->respond($variant, context: ['groups' => 'variant.show']);
        }

        return $this->respondValidatorFailed($form);
    }
}
