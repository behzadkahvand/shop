<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\RateAndReview;
use App\Form\RateAndReviewType;
use App\Service\RateAndReview\RateAndReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route("/reviews", name: "reviews.")]
class RateAndReviewController extends Controller
{
    public function __construct(
        protected EntityManagerInterface $manager,
        protected RateAndReviewService $rateAndReviewService
    ) {
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Response(
     *     response=200,
     *     description="Return list of customer rate and reviews.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=RateAndReview::class, groups={"customer.rateAndReview.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(): JsonResponse
    {
        $this->disableQueryFilters();

        $rateAndReviews = $this
            ->rateAndReviewService
            ->getFindAllCustomerRateAndReviewsQuery($this->getUser());

        return $this->respondWithPagination(
            $rateAndReviews,
            context: ['groups' => 'customer.rateAndReview.index'],
        );
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Response(
     *     response=200,
     *     description="Return list of customer bought products with no rate and review.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=RateAndReview::class, groups={"customer.rateAndReview.products"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/products", name: "products", methods: ["GET"])]
    public function productsWithNoRateAndReview(): JsonResponse
    {
        $boughtProductsWithNoRateAndReview = $this
            ->rateAndReviewService
            ->getFindAllBoughtProductsWithNoRateAndReviewQuery($this->getUser());

        return $this->respondWithPagination(
            $boughtProductsWithNoRateAndReview,
            context: ['groups' => 'customer.rateAndReview.products'],
        );
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
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update a rate and review.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=RateAndReview::class, groups={"customer.rateAndReview.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "reviews.update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, RateAndReview $rateAndReview): JsonResponse
    {
        $form = $this
            ->createForm(RateAndReviewType::class, $rateAndReview, ['method' => 'PATCH'])
            ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->rateAndReviewService->updateRateAndReview($rateAndReview);

        return $this->respond($rateAndReview, context: ['groups' => 'customer.rateAndReview.update']);
    }

    /**
     * @OA\Tag(name="Rate And Review")
     * @OA\Response(
     *     response=200,
     *     description="Delete a rate and review.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "reviews.delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(RateAndReview $rateAndReview): JsonResponse
    {
        $id = $rateAndReview->getId();

        $this->rateAndReviewService->deleteRateAndReview($rateAndReview);

        return $this->respondEntityRemoved($id);
    }

    private function disableQueryFilters(): void
    {
        $filters = [
            'inventoryIsActive',
            'inventoryHasStock',
            'inventoryConfirmedStatus',
        ];

        foreach ($filters as $filter) {
            $this->manager->getFilters()->disable($filter);
        }
    }
}
