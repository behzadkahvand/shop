<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Dictionary\InventoryStatus;
use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Service\Inventory\InitialInventoryStatus\InitialInventoryStatusService;
use App\Service\ORM\CustomFilters\Inventory\Admin\InventoryHasDiscountCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/inventories", name: "inventories.")]
class InventoryController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Inventory")
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
     *     description="Return list of inventories.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Inventory::class, groups={"inventories.index"})),
     *         ),
     *         @OA\Property(property="metas", type="object",
     *            @OA\Property(property="status", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters"= {
     *         InventoryHasDiscountCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(Inventory::class, $request->query->all()),
            context: ['groups' => 'inventories.index'],
            meta: ['status' => array_values(InventoryStatus::toArray())]
        );
    }

    /**
     * @OA\Tag(name="Inventory")
     * @OA\Response(
     *     response=200,
     *     description="Return an Inventory details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Inventory::class, groups={"inventories.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object",
     *            @OA\Property(property="status", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Inventory $inventory): JsonResponse
    {
        return $this->setMetas([
            'status' => array_values(InventoryStatus::toArray())
        ])->respond(
            $inventory,
            context: ['groups' => 'inventories.show']
        );
    }

    /**
     * @OA\Tag(name="Inventory")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="stock", type="integer"),
     *         @OA\Property(property="price", type="integer"),
     *         @OA\Property(property="finalPrice", type="integer"),
     *         @OA\Property(property="maxPurchasePerOrder", type="integer"),
     *         @OA\Property(property="suppliesIn", type="integer"),
     *         @OA\Property(property="sellerCode", type="string"),
     *         @OA\Property(property="isActive", type="boolean"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Update Inventory data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Inventory::class, groups={"inventories.update"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        Inventory $inventory,
        InitialInventoryStatusService $initialInventoryStatusService,
        MaxInventoryDiscountValidator $discountValidator
    ): JsonResponse {
        $oldLeadTime = $inventory->getLeadTime();
        $oldStock    = $inventory->getSellerStock();

        $initialPrice = $request->get('price') ?? $inventory->getPrice();
        $finalPrice   = $request->get('finalPrice') ?? $inventory->getFinalPrice();

        $discount = calc_discount($initialPrice, $finalPrice);
        $discountValidator->validate($discount);

        $form = $this->createForm(InventoryType::class, $inventory, [
            'method'     => 'PATCH',
            'updated_by' => $this->getUser()
        ]);
        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $initialInventoryStatusService->set($inventory, $oldLeadTime, $oldStock);

            $this->manager->persist($inventory);
            $this->manager->flush();

            return $this->respond($inventory, context: ['groups' => 'inventories.update']);
        }

        return $this->respondValidatorFailed($form);
    }
}
