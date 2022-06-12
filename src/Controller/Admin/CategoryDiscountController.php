<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CategoryDiscountRange;
use App\Form\CategoryDiscountType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/category-discounts", name: "category_discounts.")]
class CategoryDiscountController extends Controller
{
    /**
     * @OA\Tag(name="Category Discount")
     * @OA\Response(
     *     response=200,
     *     description="Return list of commissions.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="array",
     *             @OA\Items(ref=@Model(type=CategoryDiscountRange::class, groups={"category.discount.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(CategoryDiscountRange::class, $request->query->all()),
            context: ['groups' => 'category.discount.index']
        );
    }

    /**
     * @OA\Tag(name="Category Discount")
     * @OA\Response(
     *     response=200,
     *     description="Category discount resource",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryDiscountRange::class, groups={"category.discount.index"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(CategoryDiscountRange $discount): JsonResponse
    {
        return $this->respond($discount, context: ['groups' => 'category.discount.index']);
    }

    /**
     * @OA\Tag(name="Category Discount")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryDiscountType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Created category discount",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryDiscountRange::class, groups={"category.discount.index"})
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
    public function store(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $request->request->all();
        $form = $this->createForm(CategoryDiscountType::class)->submit($data);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $categoryDiscount = $form->getData();

        $em->persist($categoryDiscount);
        $em->flush();

        return $this->respond(
            $categoryDiscount,
            Response::HTTP_CREATED,
            context: ['groups' => 'category.discount.index']
        );
    }

    /**
     * @OA\Tag(name="Category Discount")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryDiscountType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Updated category discount",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryDiscountRange::class, groups={"category.discount.index"})
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
    public function update(Request $request, EntityManagerInterface $em, CategoryDiscountRange $discount): JsonResponse
    {
        $form = $this->createForm(CategoryDiscountType::class, $discount)
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $em->flush();

        return $this->respond($discount, context: ['groups' => 'category.discount.index']);
    }

    /**
     * @OA\Tag(name="Category Discount")
     * @OA\Response(
     *     response=200,
     *     description="Deleted category discount",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer", description="Removed discount id")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "remove", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(EntityManagerInterface $em, CategoryDiscountRange $discount): JsonResponse
    {
        $id = $discount->getId();

        $em->remove($discount);
        $em->flush();

        return $this->respondEntityRemoved($id);
    }
}
