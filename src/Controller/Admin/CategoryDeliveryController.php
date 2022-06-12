<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Delivery;
use App\Form\CategoryDeliveryType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/category-deliveries", name: "category_deliveries.")]
class CategoryDeliveryController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private QueryBuilderFilterService $filterService
    ) {
    }

    /**
     * @OA\Tag(name="Category Delivery")
     * @OA\Response(
     *     response=200,
     *     description="Return list of deliveries.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Delivery::class, groups={"category.delivery.index"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithPagination(
            $this->filterService->filter(Delivery::class, $request->query->all()),
            context: ['groups' => 'category.delivery.index']
        );
    }

    /**
     * @OA\Tag(name="Category Delivery")
     * @OA\Response(
     *     response=200,
     *     description="Return a Category Delivery details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Delivery::class, groups={"category.delivery.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Delivery $delivery): JsonResponse
    {
        return $this->respond($delivery, context: ['groups' => 'category.delivery.show']);
    }

    /**
     * @OA\Tag(name="Category Delivery")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryDeliveryType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Update Category Delivery data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Delivery::class, groups={"category.delivery.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, Delivery $delivery): JsonResponse
    {
        $form = $this->createForm(
            CategoryDeliveryType::class,
            $delivery,
            ['validation_groups' => 'category.delivery.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($delivery);
            $this->manager->flush();

            return $this->respond($delivery, context: ['groups' => 'category.delivery.update']);
        }

        return $this->respondValidatorFailed($form);
    }
}
