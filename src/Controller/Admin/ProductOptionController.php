<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Form\Type\ProductOptionType;
use App\Form\Type\ProductOptionValueType;
use App\Service\ORM\QueryBuilderFilterService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/product/options", name: "product_options.")]
class ProductOptionController extends Controller
{
    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[products.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=products.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of product options",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductOption::class, groups={"product.option"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(ProductOption::class, $request->query->all()),
            context: ['groups' => 'product.option']
        );
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Product option with given id",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOption::class, groups={"product.option"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ProductOption $option): JsonResponse
    {
        return $this->respond($option, context: ['groups' => ['product.option']]);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(
     *            property="values",
     *            type="array",
     *            @OA\Items(
     *                type="object",
     *                @OA\Property(property="value", type="string"),
     *                @OA\Property(
     *                    property="attributes",
     *                    type="array",
     *                    @OA\Items(
     *                        type="object",
     *                        @OA\Property(property="key", type="string"),
     *                        @OA\Property(property="value", type="string")
     *                    )
     *                )
     *            )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Created product option",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOption::class, groups={"product.option"})
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
            ProductOptionType::class,
            options: ['validation_groups' => ['product.option.create']]
        )->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $option = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($option);
            $entityManager->flush();

            return $this->respond(
                $option,
                Response::HTTP_CREATED,
                context: ['groups' => ['product.option']]
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(
     *            property="values",
     *            type="array",
     *            @OA\Items(
     *                type="object",
     *                @OA\Property(property="value", type="string"),
     *                @OA\Property(
     *                    property="attributes",
     *                    type="array",
     *                    @OA\Items(
     *                        type="object",
     *                        @OA\Property(property="key", type="string"),
     *                        @OA\Property(property="value", type="string")
     *                    )
     *                )
     *            )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated product option",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOption::class, groups={"product.show"})
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
    public function update(Request $request, ProductOption $option): JsonResponse
    {
        $form = $this->createForm(
            ProductOptionType::class,
            $option,
            ['validation_groups' => ['product.option.update']]
        )->submit($request->request->all(), 'PATCH' !== $request->getMethod());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->respond($option, context: ['groups' => ['product.show']]);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="value", type="string"),
     *         @OA\Property(property="code", type="string"),
     *         @OA\Property(
     *             property="attributes",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="key", type="string"),
     *                 @OA\Property(property="value", type="string")
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated product option",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOption::class, groups={"product.show"})
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
    #[Route("/{code}/values", name: "create_option_value", methods: ["POST"])]
    public function createOptionValue(Request $request, ProductOption $productOption): JsonResponse
    {
        $value = new ProductOptionValue();
        $form  = $this->createForm(ProductOptionValueType::class, $value, [
            'validation_groups' => ["admin.create"],
        ])->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $value->setOption($productOption);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($value);
            $manager->flush();

            return $this->respond($productOption, context: ['groups' => ['product.show']]);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Show product option value",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOptionValue::class, groups={"product.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/values/{id}", name: "show_option_value", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function showOptionValue(ProductOptionValue $optionValue): JsonResponse
    {
        return $this->respond($optionValue, context: ['groups' => ['product.show']]);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="value", type="string"),
     *         @OA\Property(property="code", type="string"),
     *         @OA\Property(
     *             property="attributes",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="key", type="string"),
     *                 @OA\Property(property="value", type="string")
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated product option value",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductOptionValue::class, groups={"product.show"})
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
    #[Route("/values/{id}", name: "update_option_value", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function updateOptionValue(Request $request, ProductOptionValue $productOptionValue): JsonResponse
    {
        $form = $this->createForm(ProductOptionValueType::class, $productOptionValue, [
            'validation_groups' => ['product.option.update'],
        ])->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->respond($productOptionValue, context: ['groups' => ['product.show']]);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Product Option")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[products.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=products.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of product options",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductOptionValue::class, groups={"product.option"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{code}/values", name: "option_values_index", methods: ["GET"])]
    public function optionValuesIndex(
        Request $request,
        QueryBuilderFilterService $filterService,
        ProductOption $productOption
    ): JsonResponse {
        $context = array_replace_recursive($request->query->all(), [
            'filter' => [
                'option.id' => $productOption->getId(),
            ],
        ]);

        return $this->respondWithPagination(
            $filterService->filter(ProductOptionValue::class, $context),
            context: ['groups' => 'product.option']
        );
    }
}
