<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CampaignCommission;
use App\Form\CampaignCommissionType;
use App\Repository\CampaignCommissionRepository;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/campaign-commissions", name: "campaign.commissions.")]
class CampaignCommissionController extends Controller
{
    public function __construct(private EntityManagerInterface $entityManager, private QueryBuilderFilterService $filter)
    {
    }

    /**
     * @OA\Tag(name="Campaign commissions")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[user.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=user.id",
     *     @OA\Items(type="string")
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Return list of campaign commissions",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=CampaignCommission::class, groups={"campaignCommission.show"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithPagination(
            $this->filter->filter(CampaignCommission::class, $request->query->all()),
            context: ['groups' => ['campaignCommission.show']]
        );
    }

    /**
     * @OA\Tag(name="Campaign commissions")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CampaignCommissionType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Return a Campaign commission",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CampaignCommission::class, groups={"campaignCommission.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(
        Request $request,
        CampaignCommissionRepository $repository
    ): JsonResponse {
        $form = $this->createForm(CampaignCommissionType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $campaignCommission = $form->getData();
        if (!$campaignCommission->getCategory()->isLeaf()) {
            return $this->respondWithError(
                'Campaign commissions can only be set on leaf categories',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $isThereAnotherActiveCommission = $repository->hasActiveCommissionInGivenDatePeriod(
            $campaignCommission->getCategory(),
            $campaignCommission->getBrand(),
            $campaignCommission->getSeller(),
            $campaignCommission->getStartDate(),
            $campaignCommission->getEndDate()
        );

        if ($isThereAnotherActiveCommission) {
            return $this->respondWithError(
                'There is another active commission for given criteria',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->persist($campaignCommission);
        $this->entityManager->flush();

        return $this->respond(
            $campaignCommission,
            Response::HTTP_CREATED,
            context: ['groups' => ['campaignCommission.show']]
        );
    }

    /**
     * @OA\Tag(name="Campaign commissions")
     * @OA\Response(
     *     response=200,
     *     description="Return a Campaign commission",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CampaignCommission::class, groups={"campaignCommission.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/terminate", name: "categoryCommissions.terminate", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function terminate(CampaignCommission $campaignCommission): JsonResponse
    {
        $campaignCommission->terminate();
        $this->entityManager->flush();

        return $this->respond(
            $campaignCommission,
            context: ['groups' => ['campaignCommission.show']]
        );
    }
}
