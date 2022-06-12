<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Category;
use App\Entity\CategoryProductOption;
use App\Form\Type\Admin\AddCategoryProductOptionValuesType;
use App\Form\Type\Admin\CreateCategoryProductOptionType;
use App\Service\CategoryProductOption\AddCategoryProductOptionValuesService;
use App\Service\CategoryProductOption\CreateCategoryProductOptionsService;
use App\Service\CategoryProductOption\RemoveCategoryProductOptionValueService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/category-product-options", name: "category.product_options.")]
class CategoryProductOptionController extends Controller
{
    /**
     * @OA\Tag(name="Category Product Option")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CreateCategoryProductOptionType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Create category product option",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Category::class, groups={"category.product_options.store"})
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
    public function store(
        Request $request,
        CreateCategoryProductOptionsService $createCategoryProductOptions
    ): JsonResponse {
        $form = $this->createForm(
            CreateCategoryProductOptionType::class,
            options: ['method' => 'POST']
        );

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $category = $createCategoryProductOptions->perform($data);

            return $this->respond($category, context: ['groups' => 'category.product_options.store']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Product Option")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=AddCategoryProductOptionValuesType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Add category product option values",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryProductOption::class, groups={"category.product_options.values.add"})
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
    #[Route("/{id}/values", name: "add.values", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function addOptionValues(
        CategoryProductOption $categoryProductOption,
        Request $request,
        AddCategoryProductOptionValuesService $addCategoryProductOptionValues
    ): JsonResponse {
        $form = $this->createForm(
            AddCategoryProductOptionValuesType::class,
            options: ['method' => 'POST']
        );

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $data->setCategoryProductOption($categoryProductOption);

            $categoryProductOption = $addCategoryProductOptionValues->perform($data);

            return $this->respond($categoryProductOption, context: ['groups' => 'category.product_options.values.add']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Product Option")
     * @OA\Response(
     *     response=200,
     *     description="Remove category product option value",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryProductOption::class, groups={"category.product_options.values.remove"})
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
    #[Route("/{id}/values/{optionValueId}", name: "remove.value", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function removeOptionValue(
        CategoryProductOption $categoryProductOption,
        int $optionValueId,
        RemoveCategoryProductOptionValueService $removeCategoryProductOptionValue
    ): JsonResponse {
        $categoryOption = $removeCategoryProductOptionValue->perform($categoryProductOption, $optionValueId);

        return $this->respond($categoryOption, context: ['groups' => 'category.product_options.values.remove']);
    }
}
