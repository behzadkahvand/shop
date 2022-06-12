<?php

namespace App\Controller\Seller;

use App\Controller\Controller;
use App\Entity\Inventory;
use App\Entity\ProductVariant;
use App\Form\InventoryType;
use App\Form\Type\Seller\ProductVariantAndInventoryType;
use App\Repository\InventoryRepository;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Service\Inventory\InitialInventoryStatus\InitialInventoryStatusService;
use App\Service\Inventory\Validation\InventoryPriceValidator;
use App\Service\ORM\CustomFilters\ProductVariant\Seller\SellerProductVariantsCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\ProductVariant\CreateProductVariantWithInventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/product/variants", name: "product_variants.")]
class ProductVariantController extends Controller
{
    public function __construct(
        private QueryBuilderFilterService $filterService,
        private InventoryRepository $inventoryRepository,
        private EntityManagerInterface $manager
    ) {
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Response(
     *     response=200,
     *     description="Return product variants details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductVariant::class, groups={"seller.variant.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         SellerProductVariantsCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        $context = $request->query->all();

        if (!isset($context['filter']['product.id'])) {
            $context['filter']['product.id'] = ['gt' => 0];
        }

        return $this->respondWithPagination(
            $this->filterService->filter(ProductVariant::class, $context),
            context: ['groups' => 'seller.variant.index']
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
     *         @OA\Property(property="isActive", type="boolean"),
     *         @OA\Property(property="maxPurchasePerOrder", type="integer"),
     *         @OA\Property(property="suppliesIn", type="integer"),
     *         @OA\Property(property="sellerCode", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return inventory details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Inventory::class, groups={"seller.inventory.update"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/inventory", name: "inventory.update", methods: ["PATCH"])]
    public function updateInventory(
        $id,
        Request $request,
        InitialInventoryStatusService $initialInventoryStatusService,
        MaxInventoryDiscountValidator $discountValidator,
        InventoryPriceValidator $inventoryPriceValidator
    ): JsonResponse {
        $seller    = $this->getUser();
        $inventory = $this->inventoryRepository->findOneBy(['seller' => $seller, 'variant' => $id]);
        if (!$inventory) {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        $campaignInventoriesCount = $this->inventoryRepository->countProductCampaignInventories($inventory->getProduct());
        $isCampaignProduct        = $campaignInventoriesCount > 0;

        $product = $inventory->getVariant()->getProduct();
        if (!$product->isConfirmed() && !$product->isUnavailable()) {
            return $this->respondInvalidParameters('Product should be confirmed or unavailable.');
        }

        $oldLeadTime = $inventory->getLeadTime();
        $oldStock    = $inventory->getSellerStock();

        $data = $request->request->all();

        $form = $this->createForm(
            InventoryType::class,
            $inventory,
            ['updated_by' => $seller, 'is_campaign_product' => $isCampaignProduct]
        );

        $form->submit($data, false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        if (!$isCampaignProduct) {
            $discount = calc_discount($request->get('price'), $request->get('finalPrice'));
            $discountValidator->validate($discount);
        }

        $inventoryPriceValidator->validate($inventory);

        $initialInventoryStatusService->set($inventory, $oldLeadTime, $oldStock);

        $this->manager->persist($inventory);
        $this->manager->flush();

        return $this->respond($inventory, context: ['groups' => 'seller.inventory.update']);
    }

    /**
     * @OA\Tag(name="Product Variant")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="product", type="integer", description="Product id"),
     *         @OA\Property(
     *             property="optionValues",
     *             type="array",
     *             @OA\Items(type="integer", description="Product option value id")
     *         ),
     *         @OA\Property(property="stock", type="integer"),
     *         @OA\Property(property="price", type="integer"),
     *         @OA\Property(property="finalPrice", type="integer"),
     *         @OA\Property(property="isActive", type="boolean"),
     *         @OA\Property(property="maxPurchasePerOrder", type="integer"),
     *         @OA\Property(property="suppliesIn", type="integer"),
     *         @OA\Property(property="sellerCode", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Create product variant with inventory",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ProductVariant::class, groups={"seller.variant.create"})
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
    public function store(
        Request $request,
        CreateProductVariantWithInventoryService $createProductVariantWithInventory,
        TranslatorInterface $translator
    ): JsonResponse {
        $data = $request->request->all();

        $form = $this->createForm(ProductVariantAndInventoryType::class)->submit($data);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $product                  = $form->getData()->getProduct();
        $campaignInventoriesCount = $this->inventoryRepository->countProductCampaignInventories($product);
        $isCampaignProduct        = $campaignInventoriesCount > 0;
        if ($isCampaignProduct) {
            return $this->respondWithError(
                $translator->trans('cannot_create_inventory_for_campaign_products', domain: 'exceptions'),
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $productVariantData = $form->getData();

        $productVariantData->setSeller($this->getUser());

        $variant = $createProductVariantWithInventory->perform($productVariantData);

        return $this->respond($variant, context: ['groups' => 'seller.variant.create']);
    }
}
