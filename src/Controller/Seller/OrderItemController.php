<?php

namespace App\Controller\Seller;

use App\Controller\Controller;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\SellerOrderItemRepository;
use App\Service\ORM\CustomFilters\SellerOrderItem\Seller\SellerOrderItemsCustomFilter;
use App\Service\ORM\CustomFilters\SellerOrderItem\Seller\SellerOrderItemStatusMappingCustomFilter;
use App\Service\Seller\SellerOrderItem\SellerOrderItemService;
use App\Service\SellerOrderItem\SellerOrderItemReport\SellerOrderItemReportService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route("/order-items", name: "order_items.")]
class OrderItemController extends Controller
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @OA\Tag(name="Seller Order Item")
     * @OA\Response(
     *     response=200,
     *     description="Return seller order items",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=SellerOrderItem::class, groups={"seller.order.items.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         SellerOrderItemsCustomFilter::class,
     *         SellerOrderItemStatusMappingCustomFilter::class,
     *     }
     * })
     */
    #[Route("/report", name: "report", methods: ["GET"])]
    public function report(Request $request, SellerOrderItemReportService $reportService): JsonResponse
    {
        $context = $request->query->all();

        if (!isset($context['filter']['orderItem.inventory.variant.product.title'])) {
            $context['filter']['orderItem.inventory.variant.product.id'] = ['gt' => 0];
        }

        return $this->respondWithPagination(
            $reportService->getQueryBuilder($context),
            context: ['groups' => ['seller.order.items.index']],
        );
    }

    /**
     * @OA\Tag(name="Seller Order Item")
     *
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="description", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Confirm sending an order item",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SellerPackage::class, groups={"seller.order.items.sent"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/sent", name: "confirm_sending", methods: ["PATCH"])]
    public function sent(
        Request $request,
        int $id,
        SellerOrderItemRepository $sellerOrderItemRepository,
        SellerOrderItemService $sellerOrderItemService
    ): JsonResponse {
        $seller          = $this->getUser();
        $sellerOrderItem = $sellerOrderItemRepository->findOneBy([
            'id'     => $id,
            'seller' => $seller,
        ]);

        if (null === $sellerOrderItem) {
            throw new NotFoundHttpException();
        }

        $package = $sellerOrderItemService->send($sellerOrderItem, $request->request->get('description'));

        $this->eventDispatcher->dispatch(
            new OrderBalanceAmountEvent($sellerOrderItem->getOrderItem()
                                                        ->getOrder()
                                                        ->getId())
        );

        return $this->respond($package, context: ['groups' => ['seller.order.items.sent'],]);
    }

    /**
     * @OA\Tag(name="Seller Order Item")
     *
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="description", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Confirm sending an order item",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SellerOrderItem::class, groups={"seller.order.items.index"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        SellerOrderItemRepository $sellerOrderItemRepository,
        int $id
    ): JsonResponse {
        $seller          = $this->getUser();
        $sellerOrderItem = $sellerOrderItemRepository->findOneBy(compact('id', 'seller'));

        if (null === $sellerOrderItem) {
            throw new NotFoundHttpException();
        }

        $form = $this->createFormBuilder($sellerOrderItem, ['method' => 'PATCH'])
                     ->add('description', options: [
                         'constraints' => [new NotBlank(), new Length(['max' => 128])],
                     ])
                     ->getForm()
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $em->flush();

        return $this->respond($sellerOrderItem, context: ['groups' => ['seller.order.items.index']]);
    }
}
