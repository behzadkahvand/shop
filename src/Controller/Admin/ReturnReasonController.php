<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ReturnReason;
use App\Form\ReturnReasonType;
use App\Repository\ReturnReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/return-reasons", name: "return_reasons.")]
class ReturnReasonController extends Controller
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Response(
     *     response=200,
     *     description="Returns list of return reasons",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ReturnReason::class, groups={"return_reason.show"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(ReturnReasonRepository $repository): JsonResponse
    {
        return $this->respond(
            $repository->findAll(),
            context: ['groups' => ['return_reason.show']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Response(
     *     response=200,
     *     description="Show a reason",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnReason::class, groups={"return_reason.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ReturnReason $reason): JsonResponse
    {
        return $this->respond($reason, context: ['groups' => ['return_reason.show']]);
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="reason", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Create a return reason",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnReason::class, groups={"return_reason.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(ReturnReasonType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $returnReason = $form->getData();

        $this->entityManager->persist($returnReason);
        $this->entityManager->flush();

        return $this->respond(
            $returnReason,
            Response::HTTP_CREATED,
            context: ['groups' => ['return_reason.show']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="reason", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Edit a return reason",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnReason::class, groups={"return_reason.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT"])]
    public function update(
        ReturnReason $returnReason,
        Request $request,
    ): JsonResponse {
        $form = $this->createForm(ReturnReasonType::class, $returnReason);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->entityManager->flush();

        return $this->respond(
            $returnReason,
            Response::HTTP_CREATED,
            context: ['groups' => ['return_reason.show']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Response(
     *     response=200,
     *     description="Reason successfully deleted",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(ReturnReason $reason): JsonResponse
    {
        $reasonId = $reason->getId();

        $this->entityManager->remove($reason);
        $this->entityManager->flush();

        return $this->respondEntityRemoved($reasonId);
    }
}
