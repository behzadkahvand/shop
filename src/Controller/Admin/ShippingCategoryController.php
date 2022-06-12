<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ShippingCategory;
use App\Form\Type\Admin\ShippingCategorySuggestionType;
use App\Repository\ShippingCategoryRepository;
use App\Service\ShippingCategory\ShippingCategorySuggestionService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/shipping-categories", name: "shipping_categories.")]
class ShippingCategoryController extends Controller
{
    /**
     * @OA\Tag(name="Shipping Category")
     * @OA\Response(
     *     response=200,
     *     description="Return list of shipping categories",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ShippingCategory::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(ShippingCategoryRepository $shippingCategoryRepository): JsonResponse
    {
        return $this->respond($shippingCategoryRepository->findAll());
    }

    /**
     * @OA\Tag(name="Shipping Category")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=ShippingCategorySuggestionType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Return suggested shipping category",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ShippingCategory::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/suggest", name: "suggest", methods: ["GET"])]
    public function suggest(Request $request, ShippingCategorySuggestionService $shippingCategorySuggestion): JsonResponse
    {
        $form = $this->createForm(ShippingCategorySuggestionType::class)
                     ->submit($request->query->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        return $this->respond($shippingCategorySuggestion->get($form->getData()));
    }

    /**
     * @OA\Tag(name="Shipping Category")
     * @OA\Response(
     *     response=200,
     *     description="Return shipping category details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ShippingCategory::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ShippingCategory $shippingCategory): JsonResponse
    {
        return $this->respond($shippingCategory);
    }
}
