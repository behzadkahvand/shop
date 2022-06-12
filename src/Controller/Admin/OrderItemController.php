<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\OrderItem;
use App\Exceptions\UnremovableEntityException;
use App\Form\Type\Admin\RetailPriceOrderItemType;
use App\Service\Order\DeleteOrderItem\DeleteOrderItemService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/order-items", name: "order_items.")]
class OrderItemController extends Controller
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @OA\Tag(name="Order Item")
     * @OA\Response(
     *     response=200,
     *     description="Delete order item.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order item deleted successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "delete", methods: ["DELETE"])]
    public function delete(int $id, DeleteOrderItemService $deleteOrderItemService): JsonResponse
    {
        try {
            $deleteOrderItemService->perform($id, $this->getUser());
        } catch (UnremovableEntityException $e) {
            return $this->respondWithError(
                $e->getMessage(),
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->setMessage('Order item deleted successfully.')->respond();
    }

    /**
     * @OA\Tag(name="Order Item")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="retailPrice", type="integer")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update order item retail price.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order items updated successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/retail-price", name: "update_retail", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function updateRetailPrice(
        OrderItem $orderItem,
        Request $request,
    ): JsonResponse {
        $form = $this->createForm(
            RetailPriceOrderItemType::class,
            $orderItem,
            ['validation_groups' => 'admin.order.item.update.retail_price']
        )->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var OrderItem $orderItem */
        $orderItem = $form->getData();
        $orderItem->setRetailPriceUpdatedBy($this->getUser());

        $this->entityManager->flush();
        return $this->setMessage('Order items updated successfully.')->respond();
    }
}
