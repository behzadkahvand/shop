<?php

namespace App\Controller\Seller;

use App\Controller\Controller;
use App\Dictionary\ProductStatusDictionary;
use App\DTO\Admin\ShippingCategorySuggestionData;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\CategoryClosure;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductVariant;
use App\Form\Type\Seller\ProductType;
use App\Repository\CategoryBrandSellerProductOptionRepository;
use App\Repository\CategoryRepository;
use App\Repository\InventoryRepository;
use App\Repository\ProductOptionRepository;
use App\Repository\ProductRepository;
use App\Service\ORM\CustomFilters\Brand\MultiColumnSearchCustomFilter;
use App\Service\ORM\CustomFilters\Product\Customer\TitleSearchCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\ProductSearchService;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Seller\ProductMetaProvider;
use App\Service\ShippingCategory\ShippingCategorySuggestionService;
use App\Service\Utils\Pagination\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use ReflectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/products", name: "products.")]
class ProductController extends Controller
{
    public function __construct(protected ProductRepository $productRepository)
    {
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="parent",
     *     in="query",
     *     description="Get direct children of category with this id (if any)",
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of categories.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Category::class, groups={"seller.product.categories_index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/categories", name: "categories_index", methods: ["GET"])]
    public function categories(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $parent  = $categoryRepository->find($request->query->getInt('parent', -1));
        $results = $categoryRepository->getChildrenQuery($parent, true, 'title')
                                      ->getResult();

        if (null !== $parent) {
            $results = array_map(static fn(CategoryClosure $cc) => $cc->getDescendant(), $results);
        }

        return $this->respond($results, context: ['groups' => 'seller.product.categories_index']);
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="title",
     *     in="query"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of brands.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Brand::class, groups={"seller.product.brands_index"}))
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
    #[Route("/brands", name: "brands_index", methods: ["GET"])]
    public function brands(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        $context = [
            'filter' => array_intersect_key($request->get('filter', []), ['title' => 1]),
            'sort'   => ['title'],
        ];

        return $this->respondWithPagination(
            $filterService->filter(Brand::class, $context),
            context: ['groups' => 'seller.product.brands_index']
        );
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Response(
     *     response=200,
     *     description="Return list of products",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Product::class, groups={"seller.products.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    #[Route("/me", name: "me", methods: ["GET"])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        QueryBuilderFilterService $builderFilter,
        ProductMetaProvider $metaProvider
    ): JsonResponse {
        $context = $request->query->all();

        if ($this->isNotStatusValid($context)) {
            return $this->respondInvalidParameters('Status is not valid!');
        }

        $seller           = $this->getUser();
        $checkInventories = 'seller.products.me' !== $request->attributes->get('_route');
        $queryBuilder     = $productRepository->getSellerProductsQueryBuilder($seller, $checkInventories);

        // to make QueryBuilderFilterService aware of joins applied in product repository
        $context['aliases'] = [
            Product::class        => [ProductVariant::class => 'productVariants'],
            ProductVariant::class => [Inventory::class => 'inventories'],
        ];

        return $this->respondWithPagination(
            $builderFilter->filter(Product::class, $context, $queryBuilder),
            context: ['groups' => 'seller.products.index'],
            meta: $metaProvider->resolve($seller)
        );
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *         filter[id]=10&filter[user.id]=10.
     *         valid keys: brandId, category, title, original",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of products",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Product::class, groups={"seller.product.search", "media"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         TitleSearchCustomFilter::class
     *     }
     * })
     */
    #[Route("/search", name: "search", methods: ["GET"])]
    public function search(Request $request, ProductSearchService $searchService): JsonResponse
    {
        $data           = $request->query->all();
        $data['filter'] = $data['filter'] ?? [];
        $page           = $request->get('page', 1);
        $limit          = $request->get('limit', 20);

        try {
            $searchResult = $searchService->search(
                new SearchData($data['filter'], $data['sort'] ?? []),
                new Pagination($page, $limit)
            );
        } catch (SearchDataValidationException $e) {
            return $this->respondInvalidParameters($e->getMessage());
        }

        return $this->setMetas($searchResult->getMetas())
                    ->respond(
                        $searchResult->getResults(),
                        context: ['groups' => ['seller.product.search', 'media'],]
                    );
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Response(
     *     response=200,
     *     description="Return product variants details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Product::class, groups={"seller.productVariant.index"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="isCampaignProduct", type="boolean"))
     *     )
     * )
     */
    #[Route("/{id}", name: "productVariant.index", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function productVariantIndex(int $id, InventoryRepository $inventoryRepository): JsonResponse
    {
        $product = $this->productRepository->getSellerProductById($id, $this->getUser());

        $campaignInventoriesCount = $inventoryRepository->countProductCampaignInventories($product);
        $isCampaignProduct        = $campaignInventoriesCount > 0;

        return $this->setMetas(['isCampaignProduct' => $isCampaignProduct])
                    ->respond($product, context: ['groups' => 'seller.productVariant.index']);
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Response(
     *     response=200,
     *     description="Return product options details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ProductOption::class, groups={"seller.productVariant.index"}))
     *         ),
     *         @OA\Property(property="metas", type="array", @OA\Items(type="string"))
     *     )
     * )
     */
    #[Route("/{id}/options", name: "productVariant.options.index", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function productVariantOptions(CategoryBrandSellerProductOptionRepository $repository, int $id): JsonResponse
    {
        $product = $this->productRepository->getSellerProductById($id, $this->getUser());
        $options = $product->getOptions();

        $categoryBrandSellerProductOptions = $repository->findByProduct($product);

        if (0 !== count($categoryBrandSellerProductOptions)) {
            foreach ($categoryBrandSellerProductOptions as $categoryBrandSellerProductOption) {
                $option = $categoryBrandSellerProductOption->getProductOption();

                if (!$options->contains($option)) {
                    continue;
                }

                $option->getValues()->clear();

                foreach ($categoryBrandSellerProductOption->getValues() as $value) {
                    $option->addValue($value);
                }
            }
        }

        return $this->respond($options, context: ['groups' => ['seller.productVariant.index'],]);
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="subtitle", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="link", type="string"),
     *         @OA\Property(property="weight", type="float"),
     *         @OA\Property(property="width", type="float"),
     *         @OA\Property(property="length", type="float"),
     *         @OA\Property(property="height", type="float"),
     *         @OA\Property(property="brand", type="integer", description="brand id"),
     *         @OA\Property(property="category", type="integer", description="category id"),
     *         @OA\Property(
     *             property="images",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="alt", type="string")
     *             )
     *         ),
     *         @OA\Property(
     *             property="featuredImage",
     *             type="object",
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="alt", type="string")
     *         ),
     *         @OA\Property(property="productIdentifiers", type="array", @OA\Items(type="string")),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Created product",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Product::class, groups={"seller.product.search", "seller.product.create", "media"})
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
        EntityManagerInterface $em,
        ProductOptionRepository $productOptionRepository,
        ShippingCategorySuggestionService $shippingCategorySuggestionService
    ): JsonResponse {
        $form = $this->createForm(ProductType::class, null, ['validation_groups' => 'seller.product.create'])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var Product $product */
        $product = $form->getData();

        $suggestionData = new ShippingCategorySuggestionData();
        $suggestionData->setHeight($product->getHeight())
                       ->setLength($product->getLength())
                       ->setWeight($product->getWeight())
                       ->setWidth($product->getWidth());

        $shippingCategory = $shippingCategorySuggestionService->get($suggestionData);

        $product->setStatus(ProductStatusDictionary::WAITING_FOR_ACCEPT)
                ->setSeller($this->getUser())
                ->setIsActive(false)
                ->setShippingCategory($shippingCategory);

        foreach ($productOptionRepository->getDefaultOptions() as $defaultOption) {
            $product->addOption($defaultOption);
        }

        $em->persist($product);
        $em->flush();

        return $this->respond(
            $product,
            Response::HTTP_CREATED,
            context: ['groups' => ['seller.product.search', 'media', 'seller.product.create']]
        );
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Response(
     *     response=200,
     *     description="Created product",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="itemCount", type="integer"),
     *                  @OA\Property(property="featuredImagePath", type="string"),
     *                  @OA\Property(property="featuredImageAlt", type="string"),
     *            ),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/best-seller", name: "best_seller", methods: ["GET"])]
    public function bestSeller(InventoryRepository $inventoryRepository): JsonResponse
    {
        return $this->respond($inventoryRepository->bestSellerProductIds($this->getUser()->getId()));
    }

    /**
     * @throws ReflectionException
     */
    private function isNotStatusValid(array $context): bool
    {
        return isset($context['filter']['status']) &&
            !in_array($context['filter']['status'], ProductStatusDictionary::toArray(), true);
    }
}
