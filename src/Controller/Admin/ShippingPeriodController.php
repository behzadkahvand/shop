<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ShippingPeriod;
use App\Form\ShippingPeriodType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/shipping-periods", name: "shipping_periods.")]
class ShippingPeriodController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private QueryBuilderFilterService $filterService
    ) {
    }

    /**
     * @OA\Tag(name="Shipping Period")
     * @OA\Response(
     *     response=200,
     *     description="Return list of shipping periods.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ShippingPeriod::class, groups={"shipping.period.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithPagination(
            $this->filterService->filter(ShippingPeriod::class, $request->query->all()),
            context: ['groups' => 'shipping.period.index']
        );
    }

    /**
     * @OA\Tag(name="Shipping Period")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="start", type="string", format="date-time"),
     *         @OA\Property(property="end", type="string", format="date-time"),
     *         @OA\Property(property="isActive", type="boolean")
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Create a new Shipping Period",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ShippingPeriod::class, groups={"shipping.period.store"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            ShippingPeriodType::class,
            options: ['validation_groups' => 'shipping.period.store', 'method' => 'POST']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $shippingPeriod = $form->getData();

            $this->manager->persist($shippingPeriod);
            $this->manager->flush();

            return $this->respond(
                $shippingPeriod,
                Response::HTTP_CREATED,
                context: ['groups' => 'shipping.period.store']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Shipping Period")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="isActive", type="boolean")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update Shipping Period data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ShippingPeriod::class, groups={"shipping.period.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, ShippingPeriod $shippingPeriod): JsonResponse
    {
        $form = $this->createForm(
            ShippingPeriodType::class,
            $shippingPeriod,
            ['validation_groups' => 'shipping.period.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($shippingPeriod);
            $this->manager->flush();

            return $this->respond($shippingPeriod, context: ['groups' => 'shipping.period.update']);
        }

        return $this->respondValidatorFailed($form);
    }
}
