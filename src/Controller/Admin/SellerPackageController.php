<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerPackage;
use App\Service\ORM\CustomFilters\SellerPackage\Admin\MultiColumnCustomerSearchCustomFilter;
use App\Service\ORM\CustomFilters\SellerPackage\Admin\MultiColumnSellerSearchCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/seller/packages", name: "seller_packages.")]
class SellerPackageController extends Controller
{
    public function __construct(protected EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Seller Package")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[status]=RECEIVED&
     *     filter[items.orderItems.status]=WAITING_FOR_SEND&
     *     filter[items.orderItems.orderItem.order.identifier]=1234&
     *     filter[items.orderItems.orderItem.order.customer]=customer&
     *     filter[items.orderItems.orderItem.inventory.variant.id]=1234&
     *     filter[items.orderItems.orderItem.inventory.variant.product.id]=1234&
     *     filter[seller]=seller",
     *     @OA\Items(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=product.id",
     *     @OA\Items(type="string")
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Return list of seller pacages",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=SellerPackage::class, groups={"admin.seller.order.items.index",
     *     "timestampable"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="orderItemStatus", type="array", @OA\Items(type="string")))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         MultiColumnSellerSearchCustomFilter::class,
     *         MultiColumnCustomerSearchCustomFilter::class,
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        $this->manager->getFilters()->disable("softdeleteable");

        return $this->respondWithPagination(
            $filterService->filter(SellerPackage::class, $request->query->all()),
            context: ['groups' => ['admin.seller.order.items.index', 'timestampable'],],
            meta: ['orderItemStatus' => array_values(SellerOrderItemStatus::toArray()),]
        );
    }

    /**
     * @OA\Tag(name="Seller Package")
     * @OA\Response(
     *     response=200,
     *     description="Return package details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=SellerPackage::class, groups={"seller.package.index", "timestampable"})
     *        ),
     *        @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/print", name: "showForPrint", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function showForPrint(SellerPackage $package): JsonResponse
    {
        $this->manager->getFilters()->disable("softdeleteable");

        return $this->respond($package, context: ['groups' => ['seller.package.show', 'timestampable']]);
    }
}
