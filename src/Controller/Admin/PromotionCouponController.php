<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CouponGeneratorInstruction;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Form\CouponGeneratorInstructionType;
use App\Messaging\Messages\Command\Promotion\GenerateCouponMessage;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Promotion\Exception\CouponHasEmptyCodeException;
use App\Service\Promotion\PromotionCouponFormSubmissionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class PromotionCouponController extends Controller
{
    /**
     * @Entity("promotion", expr="repository.findOneBy({id: promotion, couponBased: true})")
     *
     * @OA\Tag(name="PromotionCoupon")
     * @OA\Response(
     *     response=200,
     *     description="Return list of promotion coupons.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=PromotionCoupon::class, groups={"promotionCoupon.read"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/promotions/{promotion}/coupons", methods: ["GET"])]
    public function index(
        Request $request,
        Promotion $promotion,
        QueryBuilderFilterService $service
    ): JsonResponse {
        $context = array_replace_recursive($request->query->all(), [
            'filter' => [
                'promotion.id' => $promotion->getId(),
            ],
        ]);

        return $this->respondWithPagination(
            $service->filter(PromotionCoupon::class, $context),
            context: ['groups' => ['promotionCoupon.read']]
        );
    }

    /**
     * @OA\Tag(name="PromotionCoupon")
     * @OA\Response(
     *     response=200,
     *     description="Return details of promotion.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=PromotionCoupon::class, groups={
     *                "promotionCoupon.read",
     *                "promotionCoupon.details",
     *                "customer.list",
     *            }),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @Entity("promotion", expr="repository.findOneBy({id: promotion, couponBased: true})")
     */
    #[Route("/promotions/{promotion}/coupons/{coupon}", methods: ["GET"])]
    public function show(PromotionCoupon $coupon): JsonResponse
    {
        return $this->respond(
            $coupon,
            context: [
                'groups' => [
                    'promotionCoupon.read',
                    'promotionCoupon.details',
                    'customer.list',
                ]
            ]
        );
    }

    /**
     * @Entity("promotion", expr="repository.findOneBy({id: promotion, couponBased: true})")
     *
     * @OA\Tag(name="PromotionCoupon")
     * @OA\Post(
     *     @OA\Parameter(
     *         in="query",
     *         name="code",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="expiresAt",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="perCustomerUsageLimit",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="usageLimit",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         description="a csv file with only one column that keeps cellphone of users",
     *         in="query",
     *         name="customersCsv",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         description="array of cellphone of users",
     *         in="query",
     *         name="customers",
     *         required=false,
     *         @OA\Items(type="string")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation of promotion coupon",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="succeed", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                property="results",
     *                type="object",
     *                ref=@Model(type=PromotionCoupon::class, groups={"promotionCoupon.read"})
     *             ),
     *             @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/promotions/{promotion}/coupons", methods: ["POST"])]
    public function create(
        Promotion $promotion,
        Request $request,
        PromotionCouponFormSubmissionHandler $handler
    ): JsonResponse {
        $promotionCoupon = new PromotionCoupon();
        $promotionCoupon->setPromotion($promotion);

        try {
            $form = $handler->submit($promotionCoupon, array_merge($request->request->all(), $request->files->all()));

            if ($form->isSubmitted() && $form->isValid()) {
                return $this->json(
                    $promotionCoupon,
                    Response::HTTP_CREATED,
                    context: ['groups' => ['promotionCoupon.read'],]
                );
            }

            return $this->respondValidatorFailed($form, false);
        } catch (CouponHasEmptyCodeException $exception) {
            return $this->respondWithError(
                'Coupon must have coupon.',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * @Entity("promotion", expr="repository.findOneBy({id: promotion, couponBased: true})")
     *
     * @OA\Tag(name="PromotionCoupon")
     * @OA\Patch(
     *     @OA\Parameter(
     *         in="query",
     *         name="code",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="expiresAt",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="perCustomerUsageLimit",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         in="query",
     *         name="usageLimit",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         description="a csv file with only one column that keeps cellphone of users",
     *         in="query",
     *         name="customersCsv",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         description="array of cellphone of users",
     *         in="query",
     *         name="customers",
     *         required=false,
     *         @OA\Items(type="string")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation of promotion coupon",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="succeed", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                property="results",
     *                type="object",
     *                ref=@Model(type=PromotionCoupon::class, groups={"promotionCoupon.read"})
     *             ),
     *             @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/promotions/{promotion}/coupons/{coupon}", methods: ["PATCH"])]
    public function update(
        Promotion $promotion,
        PromotionCoupon $coupon,
        Request $request,
        PromotionCouponFormSubmissionHandler $handler
    ): JsonResponse {
        if ($promotion->getId() !== $coupon->getPromotion()->getId()) {
            throw $this->createNotFoundException();
        }

        try {
            $form = $handler->submit($coupon, array_merge($request->request->all(), $request->files->all()));
            if ($form->isSubmitted() && $form->isValid()) {
                return $this->respond(
                    $coupon,
                    Response::HTTP_CREATED,
                    context: ['groups' => ['promotionCoupon.read'],]
                );
            }

            return $this->respondValidatorFailed($form, false);
        } catch (CouponHasEmptyCodeException $exception) {
            return $this->respondWithError(
                'Coupon must has coupon.',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * @OA\Tag(name="PromotionCoupon")
     * @OA\Post(
     *     @OA\Parameter(
     *         name="Body Parameters",
     *         in="query",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="amount", type="integer"),
     *             @OA\Property(property="prefix", type="string"),
     *             @OA\Property(property="codeLength", type="integer"),
     *             @OA\Property(property="suffix", type="string"),
     *             @OA\Property(property="expiresAt", type="string", example="2020-11-01 10:00:00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation of coupon generation instruction",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="succeed", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                property="results",
     *                type="object",
     *                ref=@Model(type=CouponGeneratorInstruction::class, groups={
     *                      "couponGenerationInstruction.read",
     *                      "promotion.read"
     *                })
     *             ),
     *             @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/promotions/{promotion}/coupons/generate", methods: ["POST"])]
    public function generate(
        Promotion $promotion,
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ): JsonResponse {
        $instruction = new CouponGeneratorInstruction();
        $instruction->setPromotion($promotion);

        $form = $this->createForm(CouponGeneratorInstructionType::class, $instruction);
        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($instruction);
            $entityManager->flush();

            $bus->dispatch(async_message(new GenerateCouponMessage($instruction->getId())));

            return $this->respond(
                $instruction,
                Response::HTTP_CREATED,
                context: ['groups' => ['couponGenerationInstruction.read', 'promotion.read'],]
            );
        }

        return $this->respondValidatorFailed($form, false);
    }
}
