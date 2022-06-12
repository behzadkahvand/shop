<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\OrderCancelReason;
use App\Form\Type\Admin\OrderCancelReasonType;
use App\Repository\OrderCancelReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/order-cancel-reasons", name: "order_cancel_reasons.")]
class OrderCancelReasonController extends Controller
{
    /**
     * @OA\Tag(name="Order Cancel Reason")
     * @OA\Response(
     *     response=200,
     *     description="Return list of order cancel reasons",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=OrderCancelReason::class, groups={"admin.order_cancel_reason.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(OrderCancelReasonRepository $repository): JsonResponse
    {
        return $this->respond(
            $repository->findAll(),
            context: ['groups' => ['admin.order_cancel_reason.index'],]
        );
    }

    /**
     * @OA\Tag(name="Order Cancel Reason")
     * @OA\Response(
     *     response=200,
     *     description="Return an order cancel reasons",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderCancelReason::class, groups={"admin.order_cancel_reason.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(OrderCancelReason $reason): JsonResponse
    {
        return $this->respond($reason, context: ['groups' => ['admin.order_cancel_reason.show'],]);
    }

    /**
     * @OA\Tag(name="Order Cancel Reason")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=OrderCancelReasonType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Create and order cancel reason",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderCancelReason::class, groups={"admin.order_cancel_reason.show"})
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
        $form = $this->createForm(OrderCancelReasonType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $reason = $form->getData();

        $em->persist($reason);
        $em->flush();

        return $this->respond(
            $reason,
            Response::HTTP_CREATED,
            context: ['groups' => ['admin.order_cancel_reason.show'],]
        );
    }

    /**
     * @OA\Tag(name="Order Cancel Reason")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=OrderCancelReasonType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Update and order cancel reason",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderCancelReason::class, groups={"admin.order_cancel_reason.show"})
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
    public function update(Request $request, OrderCancelReason $reason, EntityManagerInterface $em): JsonResponse
    {
        $form = $this->createForm(OrderCancelReasonType::class, $reason)
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $em->persist($reason);
        $em->flush();

        return $this->respond($reason, context: ['groups' => ['admin.order_cancel_reason.show'],]);
    }
}
