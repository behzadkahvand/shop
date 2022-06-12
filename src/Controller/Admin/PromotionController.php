<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Promotion;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Promotion\PromotionFormSubmissionHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromotionController extends Controller
{
    /**
     * @OA\Tag(name="Promotion")
     * @OA\Response(
     *     response=200,
     *     description="Return list of promotions.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Promotion::class, groups={
     *                  "promotion.read",
     *                  "promotionRule.read",
     *                  "promotionAction.read",
     *            }))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/promotions", name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(Promotion::class, $request->query->all()),
            context: ['groups' => ['promotion.read', 'promotionRule.read', 'promotionAction.read']]
        );
    }

    /**
     * @OA\Tag(name="Promotion")
     * @OA\Post(
     *     @OA\Parameter(
     *         name="Body Parameters",
     *         in="query",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="enabled", type="boolean"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="priority", type="integer"),
     *             @OA\Property(property="usageLimit", type="integer"),
     *             @OA\Property(property="couponBased", type="boolean"),
     *             @OA\Property(property="startsAt", type="string", example="2020-11-01 10:00:00"),
     *             @OA\Property(property="endsAt", type="string", example="2020-11-01 10:00:00"),
     *             @OA\Property(
     *                 property="rules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="type",
     *                         type="string", enum={
     *                             "maximum_orders_count","category","product","minimum_basket_total","city"
     *                         }
     *                     ),
     *                     @OA\Property(
     *                         property="configurations",
     *                         type="object"
     *                     )
     *                 ),
     *                 example={
     *                     {"type"="maximum_orders_count","configuration"={"orders_count"=1}},
     *                     {"type"="category","configuration"={"category_ids"={1,2,3}}},
     *                     {"type"="product","configuration"={"product_ids"={1,2,3}}},
     *                     {"type"="minimum_basket_total","configuration"={"basket_total"=1000}},
     *                     {"type"="city","configuration"={"city_ids"={1,2,3}}}
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="actions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="type",
     *                         type="string", enum={
     *                             "fixed_discount"
     *                         }
     *                     ),
     *                     @OA\Property(
     *                         property="configurations",
     *                         type="object"
     *                     )
     *                 ),
     *                 example={
     *                     {"type"="fixed_discount","configuration"={"amount"=1000}}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation of promotion",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="succeed", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                property="results",
     *                type="object",
     *                ref=@Model(type=Promotion::class, groups={
     *                      "promotion.read",
     *                      "promotionRule.read",
     *                      "promotionAction.read",
     *                      "promotionCoupon.read",
     *                })
     *             ),
     *             @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/promotions", methods: ["POST"])]
    public function create(Request $request, PromotionFormSubmissionHandler $handler): JsonResponse
    {
        $promotion = new Promotion();
        $form      = $handler->submit($promotion, $request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->respond(
                $promotion,
                Response::HTTP_CREATED,
                context: [
                    'groups' => ['promotion.read', 'promotionRule.read', 'promotionAction.read', 'promotionCoupon.read'],
                ]
            );
        }

        return $this->respondValidatorFailed($form, false);
    }

    /**
     * @OA\Tag(name="Promotion")
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
     *            ref=@Model(type=Promotion::class, groups={"promotion.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/promotions/{id}", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Promotion $promotion): JsonResponse
    {
        return $this->respond($promotion, context: ['groups' => ['promotion.read']]);
    }

    /**
     * @OA\Tag(name="Promotion")
     * @OA\Patch(
     *     @OA\Parameter(
     *         name="Body Parameters",
     *         in="query",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="enabled", type="boolean"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="priority", type="integer"),
     *             @OA\Property(property="usageLimit", type="integer"),
     *             @OA\Property(property="couponBased", type="boolean"),
     *             @OA\Property(property="startsAt", type="string", example="2020-11-01 10:00:00"),
     *             @OA\Property(property="endsAt", type="string", example="2020-11-01 10:00:00"),
     *             @OA\Property(
     *                 property="rules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="type",
     *                         type="string", enum={
     *                             "maximum_orders_count","category","product","minimum_basket_total","city"
     *                         }
     *                     ),
     *                     @OA\Property(
     *                         property="configurations",
     *                         type="object"
     *                     )
     *                 ),
     *                 example={
     *                     {"type"="maximum_orders_count","configuration"={"orders_count"=1}},
     *                     {"type"="category","configuration"={"category_ids"={1,2,3}}},
     *                     {"type"="product","configuration"={"product_ids"={1,2,3}}},
     *                     {"type"="minimum_basket_total","configuration"={"basket_total"=1000}},
     *                     {"type"="city","configuration"={"city_ids"={1,2,3}}}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful update of promotion",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="succeed", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                property="results",
     *                type="object",
     *                ref=@Model(type=Promotion::class, groups={
     *                      "promotion.read",
     *                      "promotionRule.read",
     *                      "promotionAction.read",
     *                      "promotionCoupon.read",
     *                })
     *             ),
     *             @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/promotions/{id}", methods: ["PATCH"])]
    public function update(Promotion $promotion, Request $request, PromotionFormSubmissionHandler $handler): JsonResponse
    {
        $form = $handler->submit($promotion, $request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->respond(
                $promotion,
                Response::HTTP_CREATED,
                context: [
                    'groups' => ['promotion.read', 'promotionRule.read', 'promotionAction.read', 'promotionCoupon.read'],
                ]
            );
        }

        return $this->respondValidatorFailed($form, false);
    }
}
