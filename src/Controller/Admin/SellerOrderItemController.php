<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\SellerOrderItem;
use App\Form\Type\Admin\SellerOrderItemStatusBatchUpdateType;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/seller/order-items", name: "seller.order_items.")]
class SellerOrderItemController extends Controller
{
    /**
     * @OA\Tag(name="Seller Order Item")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[status]=RECEIVED&
     *     filter[orderItem.order.identifier]=1234&
     *     filter[orderItem.order.customer.id]=1&
     *     filter[orderItem.order.status]=WAITING_FOR_PAY&
     *     filter[orderItem.orderShipment.id]=1&
     *     filter[orderItem.orderShipment.status]=WAITING_FOR_SUPPLY&
     *     filter[orderItem.inventory.id]=1234&
     *     filter[orderItem.inventory.variant.product.id]=1234",
     *     @OA\Items(type="string")
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Return list of seller order items",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=SellerOrderItem::class, groups={"admin.seller.order_items.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        QueryBuilderFilterService $filterService
    ): JsonResponse {
        $em->getFilters()->disable('softdeleteable');

        return $this->respondWithPagination(
            $filterService->filter(SellerOrderItem::class, $request->query->all()),
            context: ['groups' => 'admin.seller.order_items.index']
        );
    }

    /**
     * @OA\Tag(name="Seller Order Item")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="status", type="string"),
     *         @OA\Property(property="ids", type="array", @OA\Items(type="integer")),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update customer data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Your message received successfully."),
     *         @OA\Property(
     *             property="results",
     *             type="array",
     *             @OA\Items(ref=@Model(type=SellerOrderItem::class, groups={"admin.seller.order_items.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/status", name: "batch.update.status", methods: ["PATCH"])]
    public function updateStatus(Request $request, SellerOrderItemStatusService $sellerOrderItemStatusService): JsonResponse
    {
        $status = $request->get('status');
        $items  = $request->get('items', []);
        $form   = $this->createForm(SellerOrderItemStatusBatchUpdateType::class)
                       ->submit(compact('status', 'items'));

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var SellerOrderItem[] $sellerOrderItems */
        $sellerOrderItems = $form->getData()['items'];
        foreach ($sellerOrderItems as $sellerOrderItem) {
            $sellerOrderItemStatusService->change($sellerOrderItem, $status);
        }

        return $this->setMessage('Order items status changed successfully.')
                    ->respond($sellerOrderItems, context: ['groups' => 'admin.seller.order_items.index']);
    }
}
