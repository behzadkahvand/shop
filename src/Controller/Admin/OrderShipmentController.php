<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Dictionary\OrderBalanceStatus;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\TransferReason;
use App\Entity\OrderShipment;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Form\TrackingCodeUploadFileType;
use App\Form\Type\Admin\MoveOrderShipmentItemsType;
use App\Form\Type\Admin\PartialOrderShipmentTransactionType;
use App\Messaging\Messages\Command\Order\ShipmentTrackingCodeUpdate;
use App\Repository\ShippingPeriodRepository;
use App\Service\File\Uploader\FileUploader;
use App\Service\Order\OrderBalanceStatus\OrderBalanceStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderShipment\DeliveryDate\OrderShipmentDeliveryDateService;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusFactory;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\PartialOrderShipment\PartialOrderShipmentService;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\PartialOrderShipmentTransactionService;
use App\Service\OrderShipment\ShipmentTrackingCodeUpdateService;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/order-shipments", name: "order_shipments.")]
class OrderShipmentController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private QueryBuilderFilterService $filterService,
        private EventDispatcherInterface $eventDispatcher,
        private TranslatorInterface $translator,
        private RecalculateOrderDocument $recalculateOrderDocument
    ) {
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Return list of order shipments.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="array",
     *             @OA\Items(ref=@Model(type=OrderShipment::class, groups={"orderShipment.index"}))
     *         ),
     *         @OA\Property(
     *              property="meta",
     *              type="object",
     *              @OA\Property(property="statuses", type="array", @OA\Items(type="string")),
     *              @OA\Property(
     *                  property="trackingCode_upload_result",
     *                  type="object",
     *                  @OA\Property(property="status", type="bool"),
     *                  @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *              )
     *         )
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(
        Request $request,
        ShipmentTrackingCodeUpdateService $trackingCodeUpdateService
    ): JsonResponse {
        $this->manager->getFilters()->enable('ShipmentHasOrderItem');

        return $this->respondWithPagination(
            $this->filterService->filter(OrderShipment::class, $request->query->all()),
            context: ['groups' => 'orderShipment.index'],
            meta: [
                'statuses'                   => array_values(OrderShipmentStatus::toArray()),
                'trackingCode_upload_result' => $trackingCodeUpdateService->getAdminUploadSheetResult($this->getUser()),
            ]
        );
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Return a Order Shipment details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=OrderShipment::class,
     *                 groups={
     *                      "orderShipment.show",
     *                      "promotionDiscount.read",
     *                      "promotionDiscount.details",
     *                      "promotionAction.read",
     *                      "timestampable",
     *                      "order.details",
     *                      "promotionCoupon.read",
     *                 }
     *             )
     *         ),
     *         @OA\Property(
     *              property="meta",
     *              type="object",
     *              @OA\Property(property="statuses", type="array", @OA\Items(type="string")),
     *              @OA\Property(property="currentStatus", type="string"),
     *              @OA\Property(property="validTransitions", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(OrderShipment $orderShipment, OrderShipmentStatusFactory $factory): JsonResponse
    {
        $currentStatus = $orderShipment->getStatus();

        try {
            $validTransitions = $factory->create($currentStatus)->validTransitions();
        } catch (InvalidOrderShipmentStatusTransitionException) {
            $validTransitions = [];
        }

        $metas = [
            'statuses'         => array_values(OrderShipmentStatus::toArray()),
            'currentStatus'    => $currentStatus,
            'validTransitions' => $validTransitions,
        ];

        return $this->setMetas($metas)
                    ->respond(
                        $orderShipment,
                        context: ['groups' => [
                            'orderShipment.show',
                            'promotionDiscount.read',
                            'promotionDiscount.details',
                            'promotionAction.read',
                            'timestampable',
                            'order.details',
                            'promotionCoupon.read',
                        ],]
                    );
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Return a Order Shipment details for driver.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=OrderShipment::class, groups={"orderShipment.show.driver"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/driver", name: "show.driver", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function showForDriver(OrderShipment $orderShipment): JsonResponse
    {
        return $this->respond($orderShipment, context: ['groups' => ['orderShipment.show.driver',],]);
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Return a Order Shipment details for print.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *              property="results",
     *              type="object",
     *              ref=@Model(type=OrderShipment::class, groups={
     *                  "orderShipment.shipmentPrint",
     *                  "customer.shipmentPrint",
     *                  "orderItem.shipmentPrint",
     *                  "orderAddress.shipmentPrint",
     *                  "hasInventory.details",
     *                  "city.details",
     *                  "province.details",
     *              })
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="balanceAmount", type="integer"))
     *     )
     * )
     */
    #[Route("/{id}/print", name: "print", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function print(
        OrderShipment $orderShipment,
        OrderBalanceStatusService $orderBalanceStatusService
    ): JsonResponse {
        $orderShipment->markAsPrinted();
        $this->manager->flush();

        $balanceData = $orderBalanceStatusService->get($orderShipment->getOrder()->getId());

        $balanceAmount = $balanceData['balanceStatus'] !== OrderBalanceStatus::CREDITOR ? $balanceData['balanceAmount'] : 0;

        return $this
            ->setMetas([
                'balanceAmount' => $balanceAmount,
            ])
            ->respond(
                $orderShipment,
                context: ['groups' => [
                    'orderShipment.shipmentPrint',
                    'customer.shipmentPrint',
                    'orderItem.shipmentPrint',
                    'orderAddress.shipmentPrint',
                    'hasInventory.details',
                    'city.details',
                    'province.details',
                ],]
            );
    }

    /**
     * @OA\Tag(name="Order Shipment")
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
     *     description="Order shipment status changed successfully.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order shipment status changed successfully."),
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
    #[Route("/{id}/status", name: "status_change", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function changeStatus(
        OrderShipment $orderShipment,
        Request $request,
        OrderShipmentStatusService $orderShipmentStatus
    ): JsonResponse {
        $data = $request->request->all();

        $form = $this->createFormBuilder()->add('status', null, [
            'constraints' => [
                new NotBlank(),
                new Choice([
                    OrderShipmentStatus::WAITING_FOR_SUPPLY,
                    OrderShipmentStatus::PREPARING,
                    OrderShipmentStatus::PREPARED,
                    OrderShipmentStatus::PACKAGED,
                    OrderShipmentStatus::WAITING_FOR_SEND,
                    OrderShipmentStatus::SENT,
                    OrderShipmentStatus::WAREHOUSE,
                    OrderShipmentStatus::DELIVERED,
                    OrderShipmentStatus::CANCELED,
                    OrderShipmentStatus::AFTER_SALES,
                    OrderShipmentStatus::RETURNING,
                    OrderShipmentStatus::RETURNED,
                    OrderShipmentStatus::THIRD_PARTY_LOGISTICS,
                    OrderShipmentStatus::CUSTOMER_ABSENCE,
                    OrderShipmentStatus::CANCELED_BY_CUSTOMER,
                    OrderShipmentStatus::NO_SEND,
                ]),
            ],
        ])->getForm();

        $form->submit($data);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $orderShipmentStatus->change($orderShipment, $data['status'], $this->getUser());

        $this->eventDispatcher->dispatch(new OrderBalanceAmountEvent($orderShipment->getOrder()->getId()));

        return $this->setMessage('Order shipment status changed successfully.')->respond();
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="grand_total", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order shipment grand total successfully changed.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(
     *            property="message",
     *            type="string",
     *            default="Order shipment grand total successfully changed."
     *         ),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.show"})
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
    #[Route("/{id}/grand-total", name: "grand_total_change", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function changeGrandTotal(
        Request $request,
        OrderShipment $shipment,
        OrderWalletPaymentHandler $walletPaymentHandler
    ): JsonResponse {
        $form = $this->createFormBuilder()
                     ->add('grand_total', NumberType::class, [
                         'constraints' => [new NotBlank(), new Range(['min' => 1, 'max' => 9999999999])],
                     ])
                     ->getForm();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $grandTotal = $form['grand_total']->getData();

        $shipment->setGrandTotal($grandTotal);

        $this->manager->flush();

        $this->recalculateOrderDocument->perform($shipment->getOrder(), true);

        $walletPaymentHandler->handle($shipment->getOrder(), TransferReason::ORDER_SHIPMENT_UPDATE);

        return $this->setMessage('Order shipment grand total successfully changed.')
                    ->respond($shipment, context: ['groups' => ['orderShipment.show'],]);
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="count",
     *     in="query",
     *     description="Change number of dates received as delivery dates"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Get order shipment delivery dates",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(
     *              property="periods",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="start", type="string", description="Start time of period"),
     *                  @OA\Property(property="end", type="string", description="End time of period")
     *              )
     *            ),
     *            @OA\Property(
     *              property="dates",
     *              type="array",
     *              @OA\Items(type="string")
     *            )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/delivery-dates", name: "delivery_dates", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function deliveryDates(
        Request $request,
        OrderShipment $shipment,
        OrderShipmentDeliveryDateService $orderShipmentDeliveryDateService
    ): JsonResponse {
        $dates = $orderShipmentDeliveryDateService->getDeliveryDatesForShipment(
            $shipment,
            $request->query->get('count', 10)
        );

        return $this->respond(
            $dates,
            context: [
                'groups'          => ['default'],
                'datetime_format' => 'Y-m-d H:i',
            ]
        );
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="delivery_date", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order shipment delivery date successfully changed.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(
     *            property="message",
     *            type="string",
     *            default="Order shipment delivery date successfully changed."
     *         ),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.show"})
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
    #[Route("/{id}/delivery-date", name: "update_delivery_date", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function updateDeliveryDate(
        Request $request,
        OrderShipment $shipment,
        ShippingPeriodRepository $shippingPeriodRepository,
        OrderShipmentDeliveryDateService $orderShipmentDeliveryDateService,
    ): JsonResponse {
        $callback = function (
            $payload,
            ExecutionContextInterface $context
        ) use (
            $orderShipmentDeliveryDateService,
            $shipment
        ) {
            if (empty($payload) || $orderShipmentDeliveryDateService->isValid($shipment, $payload)) {
                return;
            }

            $context->buildViolation('The value you selected is not a valid order shipment delivery date time.')
                    ->atPath('delivery_date')
                    ->addViolation();
        };

        $form = $this->createFormBuilder()
                     ->add('delivery_date', DateTimeType::class, [
                         'widget'      => 'single_text',
                         'constraints' => [
                             new NotBlank(),
                             new GreaterThanOrEqual('now'),
                             new Callback(compact('callback')),
                         ],
                     ])
                     ->getForm();

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $deliveryDate = $form['delivery_date']->getData();
            $period       = $shippingPeriodRepository->findOneBy([
                'isActive' => true,
                'start'    => $deliveryDate,
            ]);

            $shipment->setDeliveryDate($deliveryDate);
            $shipment->setPeriod($period);

            $this->manager->flush();

            return $this->setMessage("Order shipment delivery date successfully changed.")
                        ->respond(
                            $shipment,
                            context: [
                                'groups'          => ['orderShipment.show'],
                                'datetime_format' => 'Y-m-d H:i',
                            ]
                        );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="tracking_number", type="string"),
     *         @OA\Property(property="paid_at", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order shipment transaction created successfully!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order shipment transaction created successfully!"),
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
    #[Route("/{id}/transaction", name: "create_transaction", requirements: ["id" => "\d+"], methods: ["Post"])]
    public function createTransaction(
        int $id,
        Request $request,
        PartialOrderShipmentTransactionService $shipmentTransactionService
    ): JsonResponse {
        $form = $this->createForm(PartialOrderShipmentTransactionType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $shipmentTransactionService->create($id, $form->getData());

        return $this->setMessage('Order shipment transaction created successfully!')->respond();
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="tracking_code", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order shipment tracking code successfully changed.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(
     *            property="message",
     *            type="string",
     *            default="Order shipment tracking code successfully changed."
     *         ),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.show"})
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
    #[Route("/{id}/tracking-code", name: "update_tracking_code", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function updateTackingCode(Request $request, OrderShipment $orderShipment): JsonResponse
    {
        $form = $this->createFormBuilder()->add(
            'tracking_code',
            options: ['constraints' => [
                new NotBlank(),
            ],]
        )->getForm();

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $orderShipment->setTrackingCode($form->getData()['tracking_code']);

        $this->manager->flush();

        return $this->setMessage('Order shipment tracking code successfully changed.')
                    ->respond($orderShipment, context: ['groups' => ['orderShipment.show'],]);
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="type", type="string", enum={"express", "non_express"}),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Mark an order shipment as sent",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order shipment sent successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.index", "timestampable"})
     *        ),
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
    #[Route("/{id}/sent", name: "sent", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function sent(
        Request $request,
        OrderShipment $shipment,
        OrderShipmentStatusService $statusService
    ): JsonResponse {
        $form = $this->createFormBuilder()
                     ->add('type', ChoiceType::class, [
                         'choices' => ['express', 'non_express'],
                     ])
                     ->getForm()
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $type      = $form['type']->getData();
        $isExpress = $shipment->isExpress();

        if ('express' === $type && !$isExpress) {
            return $this->respondWithError('Shipment is not express!', status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ('non_express' === $type && $isExpress) {
            return $this->respondWithError('Shipment is not non express!', status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($shipment->isCanceled() || $shipment->isCanceledByCustomer()) {
            $description = $this->translator->trans(
                'order_shipment_canceled',
                [],
                'order_shipment',
            );

            return $this->respondWithError($description, status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$shipment->isWaitingForSend()) {
            return $this->respondWithError(
                'Only shipments with waiting for send status allowed!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $statusService->change($shipment, OrderShipmentStatus::SENT, $this->getUser());

        return $this->setMessage('Order shipment sent successfully.')
                    ->respond($shipment, context: ['groups' => ['orderShipment.index', 'timestampable'],]);
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Mark an order shipment as waiting for send",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order shipment is waiting for send successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.index", "timestampable"})
     *        ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Invalid Transition to waiting for send",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Only shipments with packaged status allowed!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/waiting-for-send", name: "waiting_for_send", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function waitingForSend(OrderShipment $shipment, OrderShipmentStatusService $statusService): JsonResponse
    {
        if ($shipment->isCanceled() || $shipment->isCanceledByCustomer()) {
            $description = $this->translator->trans('order_shipment_canceled', domain: 'order_shipment');

            return $this->respondWithError($description, status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$shipment->isPackaged()) {
            return $this->respondWithError(
                'Only shipments with packaged status allowed!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $statusService->change($shipment, OrderShipmentStatus::WAITING_FOR_SEND, $this->getUser());

        return $this->setMessage('Order shipment is waiting for send successfully.')
                    ->respond(
                        $shipment,
                        context: ['groups' => ['orderShipment.index', 'timestampable']]
                    );
    }

    /**
     * @OA\Tag(name="Order Shipment Tracking Number Batch Update")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="sheet", type="file")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Order shipment tracking file uploaded successfully!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Order shipment tracking file uploaded successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/batch-update/tracking-code", name: "upload_tracking_code_file", methods: ["POST"])]
    public function uploadFileTrackingCodes(
        Request $request,
        FileUploader $fileUploader,
        ShipmentTrackingCodeUpdateService $trackingCodeUpdateService,
        MessageBusInterface $messenger
    ): JsonResponse {
        $form = $this->createForm(TrackingCodeUploadFileType::class);
        $form->submit($request->files->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $uploadedSheetPath = $fileUploader->upload($form->get('sheet')->getData());

        $shipmentTrackingCodeUpdate = $trackingCodeUpdateService->saveUploadedSheetFile($uploadedSheetPath);

        $messenger->dispatch(new ShipmentTrackingCodeUpdate($shipmentTrackingCodeUpdate->getId()));

        return $this->setMessage('Order shipment tracking file uploaded successfully!')->respond();
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=201,
     *     description="Create cloned order shipment for partial shipment",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Clone order shipment has done successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=OrderShipment::class, groups={"orderShipment.show"})
     *        ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Invalid order shipment status to create clone",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Only shipments with WAITING_FOR_SUPPLY status allowed!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/clone/{id}", name: "create_cloned_shipment", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function cloneOrderShipment(
        OrderShipment $shipment,
        PartialOrderShipmentService $partialOrderShipmentService
    ): JsonResponse {
        if ($shipment->getStatus() != OrderShipmentStatus::WAITING_FOR_SUPPLY) {
            return $this->respondWithError(
                'Only shipments with WAITING_FOR_SUPPLY status allowed!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $clonedShipment = $partialOrderShipmentService->cloneShipment($shipment);

        return $this->setMessage('Clone order shipment has done successfully!')
                    ->respond(
                        $clonedShipment,
                        Response::HTTP_CREATED,
                        context: ['groups' => ['orderShipment.show'],]
                    );
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="items",
     *             type="array",
     *             description="Array of order item ids",
     *             @OA\Items(type="integer"),
     *         ),
     *         example={"items"={1,2,3}}
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Move given order items to given shipment has done successfully",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Move given order items to given shipment has done successfully"),
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
    #[Route("/move/items/{id}", name: "move_shipment_order_items", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function moveOrderItems(
        OrderShipment $shipment,
        Request $request,
        PartialOrderShipmentService $partialOrderShipmentService,
        OrderWalletPaymentHandler $orderWalletPaymentHandler
    ): JsonResponse {
        $form = $this->createForm(
            MoveOrderShipmentItemsType::class,
            null,
            compact('shipment')
        )->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $partialOrderShipmentService->moveItems($shipment, $form->getData()->getItems());

        $this->recalculateOrderDocument->perform($shipment->getOrder(), true);

        $orderWalletPaymentHandler->handle($shipment->getOrder(), TransferReason::ORDER_SHIPMENT_UPDATE);

        return $this->setMessage('Move given order items to given shipment has done successfully!')->respond();
    }

    /**
     * @OA\Tag(name="Order Shipment")
     * @OA\Response(
     *     response=200,
     *     description="Order shipment successfully deleted",
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
    public function delete(OrderShipment $shipment): JsonResponse
    {
        if (!$shipment->getOrderItems()->isEmpty()) {
            throw new UnauthorizedHttpException('', 'You cannot delete a shipment which has order items');
        }

        $orderShipmentId = $shipment->getId();
        $this->manager->remove($shipment);
        $this->manager->flush();

        return $this->respondEntityRemoved($orderShipmentId);
    }
}
