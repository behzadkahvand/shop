<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Order;
use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Events\Order\ReturnRequest\ReturnRequestRegistered;
use App\Events\Order\ReturnRequest\ReturnRequestStatusUpdated;
use App\Exceptions\Order\ReturnRequest\InvalidTransitionException;
use App\Form\ReturnRequestType;
use App\Form\Type\Admin\ReturnRequestTransitionType;
use App\Repository\AdminRepository;
use App\Service\Order\ReturnRequest\Refund\ReturnRequestRefundCalculator;
use App\Service\Order\ReturnRequest\Validator\ReturnRequestValidator;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Exception\UndefinedTransitionException;
use Symfony\Component\Workflow\Registry;

class ReturnRequestController extends Controller
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @OA\Tag(name="Return requests")
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns list of return requests",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ReturnRequest::class, groups={"return_request.index", "timestampable", "blameable"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/return-requests", name: "return_requests.index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(
                ReturnRequest::class,
                $request->query->all()
            ),
            context: ['groups' => ['return_request.index', 'timestampable', 'blameable']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns a return request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnRequest::class, groups={"return_request.show", "timestampable", "blameable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/return-requests/{id}", name: "return_requests.show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ReturnRequest $returnRequest, AdminRepository $repository): JsonResponse
    {
        $admin = $repository->findOneBy(['email' => $returnRequest->getCreatedBy()]);
        $returnRequest->setCreatedBy($admin->getFullName());
        return $this->respond(
            $returnRequest,
            context: ['groups' => ['return_request.show', 'timestampable', 'blameable']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns return requests of an order",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnRequest::class, groups={"return_request.show", "timestampable", "blameable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/orders/{identifier}/return-requests", name: "order_return_requests.show", requirements: ["identifier" => "\d+"], methods: ["Get"])]
    public function orderReturnRequests(Order $order): JsonResponse
    {
        return $this->respond(
            $order->getReturnRequests(),
            context: ['groups' => ['return_request.show', 'timestampable', 'blameable']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="returnDate", type="string"),
     *         @OA\Property(property="customerAddress", type="string"),
     *         @OA\Property(property="driverMobile", type="string"),
     *         @OA\Property(
     *              property="items",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="quantity", type="integer"),
     *                  @OA\Property(property="isReturnable", type="boolean"),
     *                  @OA\Property(property="orderItem", type="integer"),
     *                  @OA\Property(property="reason", type="integer"),
     *                  @OA\Property(property="description", type="string")
     *              )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Returns a return request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnRequest::class, groups={"return_request.show", "timestampable", "blameable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/orders/{identifier}/return-requests", name: "return_requests.store", requirements: ["identifier" => "\d+"], methods: ["POST"])]
    public function store(
        Order $order,
        Request $request,
        ReturnRequestValidator $validator,
        ReturnRequestRefundCalculator $refundCalculator,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $form = $this->createForm(ReturnRequestType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $returnRequest = $form->getData();
        assert($returnRequest instanceof ReturnRequest);
        $returnRequest->setOrder($order);

        $refundCalculator->calculate($returnRequest);
        $validator->validate($returnRequest);

        $dispatcher->dispatch(new ReturnRequestRegistered($returnRequest));

        $this->em->persist($returnRequest);
        $this->em->flush();

        $dispatcher->dispatch(new ReturnRequestStatusUpdated($returnRequest));

        return $this->respond(
            $returnRequest,
            Response::HTTP_CREATED,
            context: ['groups' => ['return_request.show', 'timestampable', 'blameable']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="returnDate", type="string"),
     *         @OA\Property(property="customerAddress", type="string"),
     *         @OA\Property(property="driverMobile", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns a return request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ReturnRequest::class, groups={"return_request.show", "timestampable", "blameable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/return-requests/{id}", name: "return_requests.update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        ReturnRequest $returnRequest,
        Request $request
    ): JsonResponse {
        $form = $this->createForm(ReturnRequestType::class, $returnRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->em->flush();

        return $this->respond(
            $returnRequest,
            context: ['groups' => ['return_request.show', 'timestampable', 'blameable']]
        );
    }

    /**
     * @OA\Tag(name="Return requests")
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns valid transitions of return request item",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="from", type="string"),
     *                  @OA\Property(property="to", type="string"),
     *                  @OA\Property(property="requiredData", type="array", @OA\Items(type="string")),
     *            )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/return-request-items/{id}/transitions", name: "return_requests_items.get_transitions", requirements: ["id" => "\d+"], methods: ["get"])]
    public function getValidTransitions(ReturnRequestItem $requestItem, Registry $registry): JsonResponse
    {
        $workflow    = $registry->get($requestItem);
        $transitions = $workflow->getEnabledTransitions($requestItem);

        $result = [];
        foreach ($transitions as $transition) {
            $tmp                 = [];
            $requiredData        = $workflow->getMetadataStore()->getMetadata('requiredData', $transition);
            $tmp['name']         = $transition->getName();
            $tmp['from']         = $transition->getFroms()[0];
            $tmp['to']           = $transition->getTos()[0];
            $tmp['requiredData'] = $requiredData;
            $result[]            = $tmp;
        }

        return $this->respond($result);
    }

    /**
     * @OA\Tag(name="Return requests")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="transition", type="string"),
     *         @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns a return request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/return-request-items/{id}/transitions", name: "return_requests_items.update_transition", requirements: ["id" => "\d+"], methods: ["Patch"])]
    public function updateStatus(Request $request, ReturnRequestItem $requestItem, Registry $registry): JsonResponse
    {
        $form = $this->createForm(ReturnRequestTransitionType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        try {
            $workflow = $registry->get($requestItem);
            $workflow->apply($requestItem, $form->get('transition')->getData(), $form->get('data')->getData() ?? []);

            $this->em->flush();
        } catch (UndefinedTransitionException | NotEnabledTransitionException | InvalidTransitionException $exception) {
            return $this->respondWithError(
                $exception->getMessage(),
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respond();
    }
}
