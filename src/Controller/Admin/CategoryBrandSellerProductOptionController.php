<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CategoryBrandSellerProductOption;
use App\Form\Type\Admin\CategoryBrandSellerProductOptionType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/category-brand-seller-product-options", name: "category_brand_seller.")]
class CategoryBrandSellerProductOptionController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Category Brand Seller Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Return list of category brand seller guaranties.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=CategoryBrandSellerProductOption::class, groups={"category_brand_seller_product_option.index"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(CategoryBrandSellerProductOption::class, $request->query->all()),
            context: ['groups' => ['category_brand_seller_product_option.index']],
        );
    }

    /**
     * @OA\Tag(name="Category Brand Seller Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Return a category brand seller guaranty details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryBrandSellerProductOption::class, groups={"category_brand_seller_product_option.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(CategoryBrandSellerProductOption $categoryBrandSellerProductOption): JsonResponse
    {
        return $this->respond(
            $categoryBrandSellerProductOption,
            context: ['groups' => ['category_brand_seller_product_option.show'],]
        );
    }

    /**
     * @OA\Tag(name="Category Brand Seller Product Option")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryBrandSellerProductOptionType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Create a category brand seller guaranty.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryBrandSellerProductOption::class, groups={"category_brand_seller_product_option.show"})
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
        $form = $this->createForm(CategoryBrandSellerProductOptionType::class)
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $categoryBrandSellerProductOption = $form->getData();

        $this->manager->persist($categoryBrandSellerProductOption);
        $this->manager->flush();

        return $this->respond(
            $categoryBrandSellerProductOption,
            Response::HTTP_CREATED,
            context: ['groups' => ['category_brand_seller_product_option.show'],]
        );
    }

    /**
     * @OA\Tag(name="Category Brand Seller Product Option")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryBrandSellerProductOptionType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Update a category brand seller guaranty.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryBrandSellerProductOption::class, groups={"category_brand_seller_product_option.show"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        CategoryBrandSellerProductOption $categoryBrandSellerProductOption
    ): JsonResponse {
        $form = $this->createForm(CategoryBrandSellerProductOptionType::class, $categoryBrandSellerProductOption)
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->manager->flush();

        return $this->respond(
            $categoryBrandSellerProductOption,
            context: ['groups' => ['category_brand_seller_product_option.show'],]
        );
    }

    /**
     * @OA\Tag(name="Category Brand Seller Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Entity has been removed successfully!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(CategoryBrandSellerProductOption $categoryBrandSellerGuaranty): JsonResponse
    {
        $id = $categoryBrandSellerGuaranty->getId();

        $this->manager->remove($categoryBrandSellerGuaranty);
        $this->manager->flush();

        return $this->respondEntityRemoved($id);
    }
}
