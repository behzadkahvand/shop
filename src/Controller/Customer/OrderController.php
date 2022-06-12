<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Order;
use App\Exceptions\Order\MinimumOrderException;
use App\Form\CustomerOrderAddressType;
use App\Form\Type\Customer\ShipmentDataType;
use App\Repository\OrderRepository;
use App\Service\Order\CustomerInvoiceOrderItems\CustomerInvoiceOrderItemsService;
use App\Service\Order\OrderService;
use App\Service\ORM\CustomFilters\Order\Customer\CustomerOrdersCustomFilter;
use App\Service\ORM\CustomFilters\Order\Customer\CustomerOrderStatusCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Utils\JalaliCalender;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

#[Route("/orders", name: "orders.")]
class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected QueryBuilderFilterService $filterService,
        protected EntityManagerInterface $manager,
        protected CustomerInvoiceOrderItemsService $customerInvoiceOrderItemsService,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @OA\Tag(name="Order")
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
     *
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
     *            @OA\Items(ref=@Model(type=Order::class, groups={"customer.order.index", "timestampable"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         CustomerOrdersCustomFilter::class,
     *         CustomerOrderStatusCustomFilter::class,
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithPagination(
            $this->filterService->filter(Order::class, $request->query->all()),
            context: ['groups' => ['customer.order.index', 'timestampable']]
        );
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Response(
     *     response=200,
     *     description="Return an Order details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            ref=@Model(type=Order::class, groups={"customer.order.show", "timestampable"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="invoiceDownloadable", type="boolean"))
     *     )
     * )
     */
    #[Route("/{identifier}", name: "show", requirements: ["identifier" => "\d+", "_locale"=> "en"], methods: ["GET"])]
    public function show($identifier): JsonResponse
    {
        $this->disableQueryFilters();
        $this->manager->getFilters()->enable('ShipmentHasOrderItem');

        $order = $this->orderRepository->findOneBy(['identifier' => $identifier, 'customer' => $this->getUser()]);

        if ($order !== null) {
            $invoiceOrderItems = $this->customerInvoiceOrderItemsService->get($order);

            return $this->setMetas(['invoiceDownloadable' => count($invoiceOrderItems) > 0])
                        ->respond($order, context: ['groups' => ['customer.order.show', 'timestampable']]);
        }

        throw new NotFoundHttpException(sprintf('Order with identifier %d not found!', $identifier));
    }

    /**
     * @OA\Tag(name="Order")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="patymentType", type="string"),
     *         @OA\Property(property="customerAddress", type="integer"),
     *         @OA\Property(property="gateway", type="string"),
     *         @OA\Property(property="isLegal", type="boolean"),
     *         @OA\Property(
     *             property="shipments",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="deliveryDate", type="string")
     *             )
     *         ),
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Update customer data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="identifier", type="string"),
     *            @OA\Property(property="paymentMethod", type="string"),
     *            @OA\Property(property="hasOnlinePayment", type="boolean"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $customer = $this->getUser();
        $isLegal  = $request->request->getBoolean('isLegal', false);

        if ($isLegal && !$customer->isProfileLegal()) {
            return $this->respondWithError(
                'Customer order can not be legal!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $shipments = $request->get('shipments');

        $form = $this->createFormBuilder()->add('shipments', CollectionType::class, [
            'entry_type'   => ShipmentDataType::class,
            'allow_delete' => true,
            'allow_add'    => true,
        ])->getForm();

        $form->submit(compact('shipments'));

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $shipments       = $form['shipments']->getData();
        $paymentType     = $request->get('paymentType');
        $useWallet       = $request->get('useWallet') ?? false;
        $customerAddress = $request->get('customerAddress');

        $form = $this->createForm(CustomerOrderAddressType::class);

        $form->submit(compact('customerAddress'));

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $affiliatorData = [];

        $utmSource = $request->get('utmSource');
        $utmToken  = $request->get('utmToken');

        if ($utmSource && $utmToken) {
            $affiliatorData = [
                'utmSource' => $utmSource,
                'utmToken'  => $utmToken
            ];
        }

        $customerAddress = $form->getData()->getCustomerAddress();

        try {
            $order = $this->orderService->store(
                $customer,
                $paymentType,
                $shipments,
                $customerAddress,
                $affiliatorData,
                $isLegal,
                $useWallet
            );

            return $this->respond([
                'identifier'       => $order->getIdentifier(),
                'paymentMethod'    => $order->getPaymentMethod(),
                'hasOnlinePayment' => $order->hasOnlinePayment(),
            ]);
        } catch (ValidationFailedException $exception) {
            return $this->respondValidationViolation($exception->getViolations());
        } catch (MinimumOrderException $exception) {
            return $this->respondWithError($exception->getMessage(), status: $exception->getCode());
        } catch (Throwable $exception) {
            $this->logger->debug(
                'Problem in finalizing order: ' . $exception->getMessage()
            );

            return $this->respondWithError(
                'there is a problem in storing order!',
                status: Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @Security("is_granted('ROLE_USER') and order.getCustomer().getId() === user.getId()")
     */
    #[Route("/{identifier}/invoice", name: "invoice", requirements: ["identifier" => "\d+"], methods: ["GET"])]
    public function invoice(Order $order, Pdf $pdfGenerator, string $pdfGeneratorBaseUrl): PdfResponse
    {
        $this->disableQueryFilters();

        $invoicePage = $this->renderView('customer/invoice.html.twig', [
            'baseUrl'           => $pdfGeneratorBaseUrl,
            'order'             => $order,
            'orderItems'        => $this->customerInvoiceOrderItemsService->get($order),
            'orderCreationDate' => $this->getOrderCreationDate($order),
        ]);

        $pdfGenerator->setTimeout(120);
        $pdfGenerator->setOption('enable-local-file-access', true);

        return new PdfResponse(
            $pdfGenerator->getOutputFromHtml($invoicePage),
            'timcheh-invoice-' . $order->getIdentifier() . '.pdf'
        );
    }

    private function disableQueryFilters(): void
    {
        $filters = [
            'inventoryIsActive',
            'inventoryHasStock',
            'productIsActive',
            'inventoryConfirmedStatus',
            'productWaitingForAcceptStatus',
            'productWithTrashedStatus',
        ];

        foreach ($filters as $filter) {
            $this->manager->getFilters()->disable($filter);
        }
    }

    private function getOrderCreationDate(Order $order): string
    {
        $year  = $order->getCreatedAt()->format('Y');
        $month = $order->getCreatedAt()->format('m');
        $day   = $order->getCreatedAt()->format('d');

        return JalaliCalender::toJalali($year, $month, $day);
    }
}
