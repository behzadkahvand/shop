<?php

namespace App\Controller\Seller;

use App\Controller\Controller;
use App\Entity\SellerPackage;
use App\Form\Type\Seller\Package\CreateSellerPackageDataType;
use App\Repository\SellerPackageRepository;
use App\Service\ORM\CustomFilters\SellerPackage\Seller\SellerPackagesCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Seller\SellerPackage\SellerPackageFactory;
use App\Service\Seller\SellerPackage\ValidationStrategy\SellerContextSellerOrderItemValidationStrategy;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/packages", name: "packages.")]
class PackageController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Package")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[status]=RECEIVED&
     *     filter[sentAt][gt]=2020-01-01",
     *     @OA\Items(type="string")
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Return list of seller packages",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=SellerPackage::class, groups={"seller.package.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         SellerPackagesCustomFilter::class,
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        $context = array_replace_recursive($request->query->all(), [
            'sort'   => ['-createdAt'],
            'filter' => [
                'seller.id' => $this->getUser()->getId(),
            ]
        ]);

        $queryBuilder = $filterService->filter(SellerPackage::class, $context);
        [$rootAlias] = $queryBuilder->getRootAliases();

        $queryBuilder
            ->leftJoin("{$rootAlias}.items", 'package_items')->addSelect('PARTIAL package_items.{id}')
            ->leftJoin('package_items.orderItems', 'seller_order_items')->addSelect('PARTIAL seller_order_items.{id}')
            ->leftJoin('seller_order_items.orderItem', 'order_item')->addSelect('PARTIAL order_item.{id, quantity}')
            ->getQuery()
            ->setHint(Query::HINT_REFRESH, true);

        return $this->respondWithPagination($queryBuilder, context: ['groups' => 'seller.package.index']);
    }

    /**
     * @OA\Tag(name="Package")
     * @OA\Response(
     *     response=200,
     *     description="Return package details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SellerPackage::class, groups={"seller.package.show", "timestampable"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(int $id, SellerPackageRepository $packageRepository): JsonResponse
    {
        $this->manager->getFilters()->disable('productWithTrashedStatus');
        $seller  = $this->getUser();
        $package = $packageRepository->findOneBy(compact('id', 'seller'));

        if (null === $package) {
            throw new NotFoundHttpException();
        }

        return $this->respond(
            $package,
            context: ['groups' => ['seller.package.show', 'timestampable']]
        );
    }

    /**
     * @OA\Tag(name="Package")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="items",
     *             type="array",
     *             description="Array of seller order item ids",
     *             @OA\Items(type="integer"),
     *         ),
     *         @OA\Property(
     *             property="type",
     *             type="string",
     *             description="seller package type, valid vlaues for this property are : FMCG , NON_FMCG",
     *         ),
     *         example={"items"={1,2,3} , "type"="FMCG"}
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Created package",
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
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request, SellerPackageFactory $sellerPackageFactory): JsonResponse
    {
        $form = $this->createForm(CreateSellerPackageDataType::class)
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $data               = $form->getData();
        $validationStrategy = new SellerContextSellerOrderItemValidationStrategy();
        $package            = $sellerPackageFactory->create(
            $data->getItems(),
            $data->getType(),
            $this->getUser(),
            $validationStrategy
        );

        return $this->respond(
            $package,
            Response::HTTP_CREATED,
            context: ['groups' => ['seller.order.items.sent'],]
        );
    }
}
