<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Dictionary\RateAndReviewStatus;
use App\Entity\RateAndReview;
use App\Form\RateAndReviewType;
use App\Service\ORM\CustomFilters\RateAndReview\Admin\IsBuyerCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\RateAndReview\Events\RateAndReviewAccepted;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Service\ORM\CustomFilters\RateAndReview\Admin\CustomerCustomFilter;

#[Route("/reviews", name: "reviews.")]
class RateAndReviewController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[product.id]=10&filter[inventory.id]=3&filter[order.id]=5",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=product.title",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of all customers rate and reviews.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=RateAndReview::class, groups={"admin.rateAndReview.index", "timestampable"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         IsBuyerCustomFilter::class,
     *         CustomerCustomFilter::class,
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(RateAndReview::class, $request->query->all()),
            context: ['groups' => ['admin.rateAndReview.index', 'timestampable']],
        );
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Response(
     *     response=200,
     *     description="Return a rate and reviw details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=RateAndReview::class, groups={"admin.rateAndReview.show", "timestampable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(RateAndReview $rateAndReview): JsonResponse
    {
        return $this->respond($rateAndReview, context: ['groups' => ['admin.rateAndReview.show', 'timestampable']]);
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="body", type="string"),
     *         @OA\Property(property="suggestion", type="string"),
     *         @OA\Property(property="rate", type="integer"),
     *         @OA\Property(property="anonymous", type="boolean"),
     *         @OA\Property(property="pin", type="boolean"),
     *         @OA\Property(property="status", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update (accept/reject) a rate and review.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=RateAndReview::class, groups={"admin.rateAndReview.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        RateAndReview $rateAndReview,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $previousStatus = $rateAndReview->getStatus();

        $form = $this->createForm(RateAndReviewType::class, $rateAndReview, ['updatedBy' => $this->getUser()])
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->manager->flush();

        if (RateAndReviewStatus::ACCEPTED !== $previousStatus && $rateAndReview->isAccepted()) {
            $dispatcher->dispatch(new RateAndReviewAccepted($rateAndReview));
        }

        return $this->respond($rateAndReview, context: ['groups' => 'admin.rateAndReview.update']);
    }
}
