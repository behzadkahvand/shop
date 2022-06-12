<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\DTO\OrderAddressData;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderCancelReasonOrder;
use App\Entity\OrderNote;
use App\Entity\OrderShipment;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Form\EmbeddedOrderAddressType;
use App\Form\OrderAddressType;
use App\Form\OrderNoteType;
use App\Form\OrderType;
use App\Form\Type\Admin\ChangeOrderStatusType;
use App\Form\Type\Admin\OrderBalanceRefundType;
use App\Form\Type\Admin\OrderLegalAccountType;
use App\Repository\OrderNoteRepository;
use App\Repository\OrderShipmentRepository;
use App\Repository\SellerOrderItemRepository;
use App\Service\Log\OrderLogService;
use App\Service\Order\OrderBalanceRefund\OrderBalanceRefundService;
use App\Service\Order\OrderBalanceStatus\OrderBalanceStatusService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\Order\OrderStatus\OrderStatusFactory;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\UpdateOrderItemsService;
use App\Service\Order\UpdateOrderPaymentMethod\UpdateOrderPaymentMethodService;
use App\Service\OrderLegalAccount\OrderLegalAccountService;
use App\Service\ORM\CustomFilters\Order\Admin\OrderBalanceStatusCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Utils\Error\ErrorExtractor;
use App\Service\Zones\ZoneDetector\ZoneDetector;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route("/orders", name: "orders.")]
class OrderController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ErrorExtractor $errorExtractor,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[customer.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=customer.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of orders",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Order::class, groups={
     *                "order.index",
     *                "timestampable",
     *                "promotionCoupon.read",
     *                "promotionCoupon.details",
     *                "promotion.read"
     *            }))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         OrderBalanceStatusCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(Order::class, $request->query->all()),
            context: [
                'groups' => [
                    'order.index',
                    'timestampable',
                    'promotionCoupon.read',
                    'promotionCoupon.details',
                    'promotion.read'
                ]
            ]
        );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return order statuses",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="order_status_key", type="string", default="order_status_value")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/statuses", name: "statuses", methods: ["GET"])]
    public function getStatuses(): JsonResponse
    {
        return $this->respond(OrderStatus::toArray());
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="status", type="string"),
     *         @OA\Property(property="force", type="boolean", description="if canceling an order this value should be true"),
     *         @OA\Property(property="cancelReason", type="integer", description="if canceling an order this value should be an order cancel reason id")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order status changed successfully.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order status changed successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
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
    #[Route("/{identifier}/status", name: "change_status", requirements: ["identifier" => "\d+"], methods: ["PATCH"])]
    public function changeStatus(Order $order, Request $request, OrderStatusService $orderStatus): JsonResponse
    {
        $form = $this->createForm(ChangeOrderStatusType::class, options: ['order' => $order])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $data = $form->getData();

        $this->manager->beginTransaction();

        try {
            $newStatus = $data->getStatus();

            if (OrderStatus::CANCELED === $newStatus) {
                $this->manager->persist(new OrderCancelReasonOrder($order, $data->getCancelReason()));
            }

            $orderStatus->change($order, $newStatus, $this->getUser());

            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollback();

            throw $e;
        }

        $this->dispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

        return $this->setMessage('Order status changed successfully.')->respond();
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return an order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={
     *                "order.show",
     *                "order.details",
     *                "promotionCoupon.read",
     *                "promotionDiscount.read",
     *                "promotionCoupon.read"
     *            })
     *         ),
     *         @OA\Property(
     *              property="metas",
     *              type="object",
     *              @OA\Property(property="validTransitions", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/{identifier}", name: "show", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function show(Order $order, OrderStatusFactory $orderStatusFactory): JsonResponse
    {
        $this->manager->getFilters()->disable("softdeleteable");
        try {
            $orderStatus = $orderStatusFactory->create($order->getStatus());

            $validTransitions = $orderStatus->validTransitions();
        } catch (InvalidOrderStatusTransitionException $exception) {
            $validTransitions = [];
        }

        return $this->setMetas(['validTransitions' => $validTransitions])
                    ->respond(
                        $order,
                        context: ['groups' => [
                            'order.show',
                            'timestampable',
                            'order.details',
                            'promotionDiscount.read',
                            'promotionCoupon.read',
                            'promotionCoupon.details',
                            'promotion.read',
                            'promotionDiscount.details',
                            'promotionAction.read',
                            'orderShipment.embedded.withDiscount',
                        ]]
                    );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return an order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={"order.items"})
     *         )
     *     )
     * )
     */
    #[Route("/{identifier}/items", name: "items", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function items(Order $order): JsonResponse
    {
        return $this->respond($order, context: ["groups" => ["order.items"]]);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="price", type="integer"),
     *                 @OA\Property(property="qunatity", type="integer")
     *      )
     *     )
     *   )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Return updated order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={"default"})
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
    #[Route("/{identifier}/items", name: "update_items", requirements: ["identifier" => "\d+"], methods: ["PATCH"])]
    public function updateItems(
        Order $order,
        Request $request,
        UpdateOrderItemsService $updateOrderItems
    ): JsonResponse {
        $data = $request->request->all();

        $violations = $this->validator->validate($data, new Collection([
            'fields' => [
                'items' => [
                    new NotBlank(),
                    new All(
                        new Collection([
                            'id'       => new NotBlank(),
                            'price'    => new Optional(new NotBlank()),
                            'quantity' => new Optional(new NotBlank()),
                        ])
                    ),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            $errors = $this->errorExtractor->extract($violations);

            return $this->respondWithError(
                'Validation error has been detected!',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order = $updateOrderItems->perform($order->getId(), $data['items'], $this->getUser());

        return $this->setMessage('Order items updated successfully.')->respond($order);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="payment_method", type="string"),
     *     )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Return updated order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={"default"})
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
    #[Route("/{identifier}/payment-method", name: "update_payment_methods", requirements: ["identifier" => "\d+"], methods: ["PATCH"])]
    public function updatePaymentMethod(
        Order $order,
        Request $request,
        UpdateOrderPaymentMethodService $updateOrderPaymentMethod
    ): JsonResponse {
        $data = $request->request->all();

        $violations = $this->validator->validate($data, new Collection([
            'fields' => [
                'payment_method' => [new NotBlank(), new Choice(['choices' => OrderPaymentMethod::toArray()])],
            ],
        ]));

        if (count($violations) > 0) {
            $errors = $this->errorExtractor->extract($violations);

            return $this->respondWithError(
                'Validation error has been detected!',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order = $updateOrderPaymentMethod->perform($order->getId(), $data['payment_method']);

        return $this->setMessage('Order payment method updated successfully.')->respond($order);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="address_id", type="integer"),
     *     )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Return updated order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderAddress::class, groups={"order_address.show"})
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
    #[Route("/{identifier}/delivery-address", name: "delivery_address_change", requirements: ["identifier" => "\d+"], methods: ["PATCH"])]
    public function changeAddress(Request $request, Order $order): JsonResponse
    {
        $address = new OrderAddressData($order);
        $form    = $this->createForm(OrderAddressType::class, $address, [
            'method'            => $request->getMethod(),
            'validation_groups' => ['order.change_address'],
        ]);

        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $orderAddress = $this->manager->getConnection()->transactional(function () use ($order, $address) {
            $orderAddress = OrderAddress::fromCustomerAddress($address->getAddress(), $order);

            $this->manager->persist($orderAddress);
            $this->manager->flush();

            return $orderAddress;
        });

        $this->manager->flush();

        return $this->setMessage('Order delivery address successfully changed.')
                    ->respond($orderAddress, context: ['groups' => ['order_address.show'],]);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return list of order shipments",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=OrderShipment::class, groups={"orderShipment.show"}))
     *         ),
     *         @OA\Property(
     *              property="metas",
     *              type="object",
     *              @OA\Property(property="statuses", type="array", @OA\Items(type="string")),
     *              @OA\Property(property="zoneName", type="string")
     *         )
     *     )
     * )
     */
    #[Route("/{identifier}/shipments", name: "shipments_index", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function shipments(
        OrderShipmentRepository $orderShipmentRepository,
        Order $order,
        ZoneDetector $zoneDetector
    ): JsonResponse {
        $this->manager->getFilters()->disable("softdeleteable");
        $zone = $zoneDetector->getZoneForOrderAddress($order->getOrderAddress());

        return $this
            ->setMetas([
                'statuses' => array_values(OrderShipmentStatus::toArray()),
                'zoneName' => $zone->getName(),
            ])
            ->respond(
                $orderShipmentRepository->findBy(['order' => $order->getId()]),
                context: ['groups' => ['orderShipment.show']]
            );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return order balance status data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="orderDocumentAmount", type="integer"),
     *            @OA\Property(property="totalTransactionAmounts", type="integer"),
     *            @OA\Property(property="totalOrderRefundDocumentAmounts", type="integer"),
     *            @OA\Property(property="totalRefundTransactionAmounts", type="integer"),
     *            @OA\Property(property="balanceAmount", type="integer"),
     *            @OA\Property(property="balanceStatus", type="string"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{identifier}/balance-status", name: "balance_status", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function balanceStatus(Order $order, OrderBalanceStatusService $orderBalanceStatusService): JsonResponse
    {
        return $this->respond($orderBalanceStatusService->get($order->getId()));
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="tracking_number", type="string"),
     *         @OA\Property(property="paid_at", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="amount", type="integer", description="this parameter is optional for changing amount transaction."),
     *         @OA\Property(property="force", type="boolean", description="for changing amount transaction this value should be true."),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order balance refund performed successfully!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order balance refund performed successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
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
    #[Route("/{identifier}/balance-refund", name: "balance_refund", requirements: ["identifier" => "\d+"], methods: ["POST"])]
    public function balanceRefund(Order $order, Request $request, OrderBalanceRefundService $orderBalanceRefundService): JsonResponse
    {
        $form = $this->createForm(OrderBalanceRefundType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $orderBalanceRefundService->add($order->getId(), $form->getData());

        return $this->setMessage('Order balance refund performed successfully!')->respond();
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=OrderNote::class, groups={"order.notes.add"})))
     * @OA\Response(
     *     response=200,
     *     description="Add a new Note on order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderNote::class, groups={"order.notes.add", "timestampable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{identifier}/notes", name: "add_note", requirements: ["identifier" => "\d+"], methods: ["POST"])]
    public function addNote(Request $request, Order $order): JsonResponse
    {
        $form = $this->createForm(
            OrderNoteType::class,
            options: ['validation_groups' => 'order.notes.add', 'method' => 'POST']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderNote = $form->getData();

            $orderNote->setAdmin($this->getUser());
            $orderNote->setOrder($order);

            $this->manager->persist($orderNote);
            $this->manager->flush();

            return $this->setMessage('Add Note has been successfully.')
                        ->respond(
                            $orderNote,
                            Response::HTTP_CREATED,
                            context: ['groups' => ['order.notes.add', 'timestampable']]
                        );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return list of order notes.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=OrderNote::class, groups={"order.notes.index", "timestampable"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{identifier}/notes", name: "list_notes", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function listNotes(Order $order, OrderNoteRepository $noteRepository): JsonResponse
    {
        return $this->respondWithPagination(
            $noteRepository->findByOrder($order),
            context: ['groups' => ['order.notes.index', 'timestampable']],
        );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="status", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order item status changed successfully.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order item status changed successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
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
    #[Route("/{orderId}/items/{itemId}/status", name: "change_item_status", methods: ["PATCH"])]
    public function changeItemStatus(
        Request $request,
        SellerOrderItemRepository $itemRepository,
        SellerOrderItemStatusService $sellerOrderItemStatusService,
        int $orderId,
        int $itemId
    ): JsonResponse {
        $sellerOrderItem = $itemRepository->findByOrderAndOrderItemId($orderId, $itemId);

        if (null === $sellerOrderItem) {
            $message = sprintf('Order item with id %d for order with id %d not found', $itemId, $orderId);

            throw new NotFoundHttpException($message);
        }

        $status = $request->request->get('status');
        $form   = $this->createFormBuilder()
                       ->add('status', ChoiceType::class, [
                           'choices'     => SellerOrderItemStatus::toArray(),
                           'constraints' => [new NotBlank()],
                       ])
                       ->getForm()
                       ->submit(compact('status'));

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $sellerOrderItemStatusService->change($sellerOrderItem, $status);

        $this->dispatcher->dispatch(new OrderBalanceAmountEvent($orderId));

        return $this->setMessage('Order items status changed successfully.')->respond();
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="promotionCoupon", type="integer"),
     *         @OA\Property(property="promotionLocked", type="boolean"),
     *         @OA\Property(
     *             property="orderAddress",
     *             type="object",
     *             @OA\Property(property="fullAddress", type="string"),
     *             @OA\Property(property="floor", type="string"),
     *             @OA\Property(property="unit", type="integer"),
     *             @OA\Property(property="number", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="family", type="string"),
     *             @OA\Property(property="nationalCode", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="postalCode", type="string")
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return an order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={
     *                "order.show",
     *                "order.details",
     *                "promotionCoupon.read",
     *                "promotionDiscount.read",
     *                "promotionCoupon.read"
     *            })
     *         ),
     *         @OA\Property(
     *              property="metas",
     *              type="object",
     *              @OA\Property(property="validTransitions", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/{identifier}", requirements: ["identifier" => "\d+"], methods: ["PATCH"])]
    public function updateOrder(
        Order $order,
        Request $request,
        OrderStatusFactory $orderStatusFactory,
        OrderService $orderService
    ): JsonResponse {
        $form = $this->createForm(OrderType::class, $order);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('promotionCoupon')->isSubmitted()) {
                $promotionCoupon = $form->get('promotionCoupon')->getData();
                $orderService->setPromotionCoupon($order, $promotionCoupon);
            }

            $this->manager->flush();
            $this->dispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

            try {
                $orderStatus = $orderStatusFactory->create($order->getStatus());

                $validTransitions = $orderStatus->validTransitions();
            } catch (InvalidOrderStatusTransitionException $exception) {
                $validTransitions = [];
            }

            return $this->setMetas(['validTransitions' => $validTransitions])
                        ->respond(
                            $order,
                            context: ['groups' => [
                                'order.show',
                                'timestampable',
                                'order.details',
                                'promotionDiscount.read',
                                'promotionCoupon.read'
                            ]]
                        );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="fullAddress", type="string"),
     *         @OA\Property(property="unit", type="string"),
     *         @OA\Property(property="floor", type="string"),
     *         @OA\Property(property="number", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="nationalCode", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="postalCode", type="string"),
     *         @OA\Property(property="city", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Create new order address for an order an make it order's default address",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order item status changed successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderAddress::class, groups={
     *                "order.show",
     *                "timestampable",
     *                "order.details",
     *            })
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
    #[Route("/{identifier}/addresses", name: "replace_order_address", requirements: ["identifier" => "\d+"], methods: ["POST"])]
    public function updateOrderAddress(
        Request $request,
        Order $order
    ): JsonResponse {
        $form = $this->createForm(EmbeddedOrderAddressType::class, options: ['validation_groups' => ["order.update"]])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $orderAddress = $form->getData();

        $order->addOrderAddress($orderAddress);

        $this->manager->persist($orderAddress);
        $this->manager->flush();

        return $this->respond(
            $orderAddress,
            context: ['groups' => [
                'order.show',
                'timestampable',
                'order.details',
            ],]
        );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="province", type="integer"),
     *         @OA\Property(property="city", type="integer"),
     *         @OA\Property(property="organizationName", type="string"),
     *         @OA\Property(property="economicCode", type="integer"),
     *         @OA\Property(property="nationalId", type="string"),
     *         @OA\Property(property="registrationId", type="string"),
     *         @OA\Property(property="phoneNumber", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Store Order Legal Account",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Order::class, groups={"order.legal.account.store"})
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
    #[Route("/{identifier}/legal-accounts", name: "legal_accounts.store", requirements: ["identifier" => "\d+"], methods: ["PUT"])]
    public function storeLegalAccount(
        Order $order,
        Request $request,
        OrderLegalAccountService $orderLegalAccountService
    ): JsonResponse {
        $form = $this->createForm(OrderLegalAccountType::class, options: ['method' => 'PUT'])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $order = $orderLegalAccountService->store($order, $form->getData());

        $this->manager->flush();

        return $this->setMessage('Order legal account is stored successfully!')
                    ->respond($order, context: ['groups' => 'order.legal.account.store']);
    }


    /**
     * @OA\Tag(name="Order Tracking")
     * @OA\Response(
     *     response=200,
     *     description="Return list of order tracking history",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(
     *            property="orderStatusLogs",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="statusFrom", type="string"),
     *                 @OA\Property(property="statusTo", type="string"),
     *                 @OA\Property(property="updatedAt", type="string"),
     *                 @OA\Property(property="updatedBy", type="string")
     *             )
     *            ),
     *           @OA\Property(
     *            property="orderShipmentStatusLogs",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="orderShipmentId", type="integer"),
     *                 @OA\Property(property="orderShipmentTitle", type="string"),
     *                 @OA\Property(property="statusFrom", type="string"),
     *                 @OA\Property(property="statusTo", type="string"),
     *                 @OA\Property(property="updatedAt", type="string"),
     *                 @OA\Property(property="updatedBy", type="string")
     *             )
     *            ),
     *           @OA\Property(
     *            property="orderItemsLogs",
     *            type="object",
     *            @OA\Property(
     *            property="deletedLogs",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="orderItemId", type="integer"),
     *                 @OA\Property(property="inventoryId", type="integer"),
     *                 @OA\Property(property="productId", type="integer"),
     *                 @OA\Property(property="productTitle", type="string"),
     *                 @OA\Property(property="deletedAt", type="string"),
     *                 @OA\Property(property="deletedBy", type="string")
     *             )
     *            ),
     *          @OA\Property(
     *            property="quantityLogs",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="orderItemId", type="integer"),
     *                 @OA\Property(property="inventoryId", type="integer"),
     *                 @OA\Property(property="productId", type="integer"),
     *                 @OA\Property(property="productTitle", type="string"),
     *                 @OA\Property(property="quantityFrom", type="integer"),
     *                 @OA\Property(property="quantityTo", type="integer"),
     *                 @OA\Property(property="updatedAt", type="string"),
     *                 @OA\Property(property="updatedBy", type="string")
     *             )
     *            )
     *            ),
     *           @OA\Property(
     *            property="sellerOrderItemStatusLogs",
     *            type="array",
     *            @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="orderItemId", type="integer"),
     *                 @OA\Property(property="inventoryId", type="integer"),
     *                 @OA\Property(property="productId", type="integer"),
     *                 @OA\Property(property="productTitle", type="string"),
     *                 @OA\Property(property="statusFrom", type="string"),
     *                 @OA\Property(property="statusTo", type="string"),
     *                 @OA\Property(property="updatedAt", type="string"),
     *                 @OA\Property(property="updatedBy", type="string")
     *             )
     *            )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/tracking/{identifier}", name: "order-tracking", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function orderTracking(int $identifier, OrderLogService $orderLogService): JsonResponse
    {
        return $this->respond($orderLogService->getOrderLogsTracking($identifier));
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return order balance amount",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order balance amount refreshed successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{identifier}/balance-amount", name: "balance-amount.refresh", requirements: ["identifier" => "\d+"], methods: ["POST"])]
    public function refreshBalanceAmount(Order $order, RecalculateOrderDocument $recalculateOrderDocument): JsonResponse
    {
        $recalculateOrderDocument->perform($order, true);

        $this->dispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

        return $this->setMessage('Order balance amount refreshed successfully.')->respond();
    }
}
