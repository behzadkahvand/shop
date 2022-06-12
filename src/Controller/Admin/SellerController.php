<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Seller;
use App\Events\Auth\UserCredentialsChanged;
use App\Events\Auth\UserDeactivated;
use App\Form\SellerType;
use App\Service\Notification\DTOs\Seller\SellerPanelAccountSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\ORM\CustomFilters\Seller\Admin\MultiColumnSearchCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Seller\SellerIdentifier\SellerIdentifierService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/sellers", name: "sellers.")]
class SellerController extends Controller
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * @OA\Tag(name="Seller")
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
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of sellers",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Seller::class, groups={"seller.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         MultiColumnSearchCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(QueryBuilderFilterService $filterService, Request $request): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(Seller::class, $request->query->all()),
            context: ['groups' => ['seller.index']]
        );
    }

    /**
     * @OA\Tag(name="Seller")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="isLimited", type="boolean"),
     *         @OA\Property(property="isRetail", type="boolean"),
     *         @OA\Property(property="pickup", type="boolean"),
     *         @OA\Property(property="fullName", type="string"),
     *         @OA\Property(property="shebaNumber", type="string"),
     *         @OA\Property(property="paymentPeriod", type="integer"),
     *         @OA\Property(property="checkoutPeriod", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Created seller",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Seller::class, groups={"seller.create"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(
        Request $request,
        UserPasswordHasherInterface $hasher,
        SellerIdentifierService $sellerIdentifier,
        NotificationService $notificationService
    ): JsonResponse {
        $form = $this->createForm(SellerType::class, options: ['validation_groups' => ['seller.create', 'create']])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var Seller $seller */
        $seller = $form->getData();
        $seller->setIsActive(true);
        $seller->setPassword($hasher->hashPassword($seller, $seller->getPlainPassword()));
        $seller->eraseCredentials();

        $this->entityManager->persist($seller);
        $this->entityManager->flush();

        $seller->setIdentifier($sellerIdentifier->generate($seller->getId()));

        $this->entityManager->flush();

        $notificationService->send(new SellerPanelAccountSmsNotificationDTO($seller));

        return $this->respond($seller, Response::HTTP_CREATED, context: ['groups' => ['seller.create']]);
    }

    /**
     * @OA\Tag(name="Seller")
     * @OA\Response(
     *     response=200,
     *     description="Return an Seller details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Seller::class, groups={"seller.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Seller $seller): JsonResponse
    {
        return $this->respond($seller, context: ['groups' => ['seller.show']]);
    }

    /**
     * @OA\Tag(name="Seller")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="isLimited", type="boolean"),
     *         @OA\Property(property="isRetail", type="boolean"),
     *         @OA\Property(property="pickup", type="boolean"),
     *         @OA\Property(property="fullName", type="string"),
     *         @OA\Property(property="shebaNumber", type="string"),
     *         @OA\Property(property="paymentPeriod", type="integer"),
     *         @OA\Property(property="checkoutPeriod", type="integer"),
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Updated seller",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Seller::class, groups={"seller.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT", "PATCH"])]
    public function update(
        Request $request,
        Seller $seller,
        UserPasswordHasherInterface $hasher,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $sellerWasActive = $seller->isActive();

        $form = $this->createForm(
            SellerType::class,
            $seller,
            [
                'validation_groups' => ['seller.update', 'update'],
                'method'            => $request->getMethod(),
            ]
        )->submit($request->request->all(), $request->getMethod() !== 'PATCH');

        if ($form->isSubmitted() && $form->isValid()) {
            if ($seller->getPlainPassword()) {
                $seller->setPassword($hasher->hashPassword($seller, $seller->getPlainPassword()));
                $seller->eraseCredentials();
                $dispatcher->dispatch(new UserCredentialsChanged($seller));
            }

            $this->entityManager->flush();

            if (!$seller->isActive() && $sellerWasActive) {
                $dispatcher->dispatch(new UserDeactivated($seller));
            }

            return $this->respond($seller, context: ['groups' => 'seller.update']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Seller")
     * @OA\Response(
     *     response=200,
     *     description="Seller successfully deleted",
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
    public function delete(Seller $seller): JsonResponse
    {
        $id = $seller->getId();

        $this->entityManager->remove($seller);
        $this->entityManager->flush();

        return $this->respondEntityRemoved($id);
    }
}
