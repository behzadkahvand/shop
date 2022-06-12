<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Form\NotifyMeType;
use App\Repository\ProductNotifyRequestRepository;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestAlreadyExistsException;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestNotFoundException;
use App\Service\Product\NotifyMe\NotifyMeService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route("/notify-me", name: "notify-me.")]
class ProductNotifyMeController extends Controller
{
    public function __construct(private NotifyMeService $notifyMeService)
    {
    }

    /**
     * @OA\Tag(name="Customer Notify-me")
     * @OA\Response(
     *     response=200,
     *     description="Return list of user product notify requests",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductNotifyRequest::class, groups={"notify.read"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(ProductNotifyRequestRepository $repository): JsonResponse
    {
        return $this->respondWithPagination(
            $repository->getAllByCustomerQuery($this->getUser()->getId()),
            context: ['groups' => 'notify.read']
        );
    }


    /**
     * @OA\Tag(name="Customer Notify-me")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="product", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Add a product to user product notify requests",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductNotifyRequest::class, groups={"notify.read"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "store", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function store(Product $product): JsonResponse
    {
        $form = $this->createForm(
            NotifyMeType::class,
            options: ['method' => 'POST',]
        );
        $form->submit(['customer' => $this->getUser()->getId(), 'product' => $product->getId()]);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var ProductNotifyRequest $notifyRequest */
        $notifyRequest = $form->getData();

        try {
            $notifyRequest = $this->notifyMeService->makeRequest($notifyRequest);
        } catch (NotifyRequestAlreadyExistsException $ex) {
            return $this->respondWithError($ex->getMessage(), status: Response::HTTP_CONFLICT);
        }

        return $this->respond($notifyRequest, Response::HTTP_CREATED, context: ['groups' => 'notify.read']);
    }

    /**
     * @OA\Tag(name="Customer Notify-me")
     * @OA\Response(
     *     response=200,
     *     description="Delete a customer notify request.",
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
    #[Route("/{id}", name: "destroy", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->notifyMeService->removeRequest($this->getUser(), $product);
        } catch (NotifyRequestNotFoundException $ex) {
            return $this->respondWithError($ex->getMessage(), status: Response::HTTP_NOT_FOUND);
        }

        return $this->respondEntityRemoved($product->getId());
    }
}
