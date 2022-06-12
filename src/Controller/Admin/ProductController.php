<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Product;
use App\Form\Type\Admin\ImportDkProductsType;
use App\Form\Type\Admin\ImportDkSellerProductsType;
use App\Form\Type\Admin\ProductReferencePriceType;
use App\Form\Type\ProductType;
use App\Messaging\Messages\Command\Product\ImportDigikalaSellerProducts;
use App\Messaging\Messages\Command\Product\ImportProductFromDigikala;
use App\Messaging\Messages\Command\Product\NotifyAvailableProduct;
use App\Messaging\Messages\Command\Product\UpdateFromOutsource;
use App\Repository\ProductOptionRepository;
use App\Service\Digikala\DigikalaProductLink;
use App\Service\Digikala\DigikalaSellerPageLink;
use App\Service\ORM\CustomFilters\Product\Admin\CategoryProductsCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Utils\Error\ErrorExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route("/products", name: "products.")]
class ProductController extends Controller
{
    public function __construct(protected HttpClientInterface $client)
    {
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[shippingCategory.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=shippingCategory.id",
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
     *            @OA\Items(ref=@Model(type=Product::class, groups={"product.index", "product.index.media"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         CategoryProductsCustomFilter::class,
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        $queryBuilder = $service->filter(Product::class, []);
        [$rootAlias] = $queryBuilder->getRootAliases();
        $fields = ['id', 'title', 'isActive', 'status', 'referencePrice', 'priceTopMargin', 'priceBottomMargin'];

        $queryBuilder->select(sprintf('PARTIAL %s.{%s}', $rootAlias, implode(', ', $fields)));

        return $this->respondWithPagination(
            $service->filter(Product::class, $request->query->all(), $queryBuilder),
            context: ['groups' => ['product.index', 'product.index.media']]
        );
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Response(
     *     response=200,
     *     description="Product with given id",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Product::class, groups={"product.show", "media"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Product $product): JsonResponse
    {
        return $this->respond($product, context: ['groups' => ['product.show', 'media']]);
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
     *         @OA\Property(property="alternativeTitle", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="isActive", type="boolean"),
     *         @OA\Property(property="weight", type="float"),
     *         @OA\Property(property="width", type="float"),
     *         @OA\Property(property="length", type="float"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="additionalTitle", type="string"),
     *         @OA\Property(property="EAV", type="string"),
     *         @OA\Property(property="summaryEAV", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="status", type="boolean"),
     *         @OA\Property(property="brand", type="integer", description="brand id"),
     *         @OA\Property(property="category", type="integer", description="category id"),
     *         @OA\Property(property="shippingCategory", type="integer", description="shipping category id"),
     *         @OA\Property(property="options", type="array", @OA\Items(type="integer", description="option id")),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Created product",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Product::class, groups={"product.create"})
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
    public function store(Request $request, ProductOptionRepository $repository): JsonResponse
    {
        $form = $this->createForm(
            ProductType::class,
            options: [
                'validation_groups' => ['create'],
            ]
        )->submit($request->request->all());

        if ($form->isValid()) {
            /** @var Product $product */
            $product = $form->getData();

            $product->setDescription(
                $product->getDescription() ?
                    strip_base64_encoded_img($product->getDescription()) :
                    null
            );

            foreach ($repository->getDefaultOptions() as $defaultOption) {
                if (!$product->getOptions()->contains($defaultOption)) {
                    $product->addOption($defaultOption);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->respond(
                $product,
                Response::HTTP_CREATED,
                context: ['groups' => ['product.create']]
            );
        }

        return $this->respondValidatorFailed($form);
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
     *         @OA\Property(property="alternativeTitle", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="isActive", type="boolean"),
     *         @OA\Property(property="weight", type="float"),
     *         @OA\Property(property="width", type="float"),
     *         @OA\Property(property="length", type="float"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="additionalTitle", type="string"),
     *         @OA\Property(property="EAV", type="string"),
     *         @OA\Property(property="summaryEAV", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="status", type="boolean"),
     *         @OA\Property(property="brand", type="integer", description="brand id"),
     *         @OA\Property(property="category", type="integer", description="category id"),
     *         @OA\Property(property="shippingCategory", type="integer", description="shipping category id"),
     *         @OA\Property(property="options", type="array", @OA\Items(type="integer", description="option id")),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated product",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Product::class, groups={"product.update"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT", "PATCH"])]
    public function update(Request $request, Product $product, MessageBusInterface $bus): JsonResponse
    {
        $cloneProduct = clone $product;

        $form = $this->createForm(
            ProductType::class,
            $product,
            [
                'validation_groups' => ['create'],
                'method'            => $request->getMethod(),
            ]
        )->submit($request->request->all(), 'PATCH' !== $request->getMethod());

        if ($form->isValid()) {
            $description = $product->getDescription() ?? $cloneProduct->getDescription();

            $product->setDescription(
                $description ?
                    strip_base64_encoded_img($description) :
                    null
            );

            $this->getDoctrine()->getManager()->flush();

            $this->dispatchNotifyProduct($cloneProduct, $product, $bus);

            return $this->respond($product, context: ['groups' => ['product.update']]);
        }

        return $this->respondValidatorFailed($form);
    }

    private function dispatchNotifyProduct(Product $cloneProduct, Product $product, MessageBusInterface $bus): void
    {
        if ($cloneProduct->getStatus() === 'UNAVAILABLE' && $product->getStatus() === 'CONFIRMED') {
            $bus->dispatch(async_message(new NotifyAvailableProduct($cloneProduct->getId())));
        }
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="digikalaDkp", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Request submitted successfully",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/outsource-update", name: "outsource_update", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function outsourceUpdate(
        Request $request,
        Product $product,
        MessageBusInterface $bus,
        ValidatorInterface $validator,
        ErrorExtractor $errorExtractor,
        EntityManagerInterface $em,
    ): JsonResponse {
        $violations = $validator->validate($request->request->all(), new Collection([
            'fields' => [
                'digikalaDkp' => [
                    new NotBlank(),
                    new NotNull()
                ],
            ],
        ]));

        if (count($violations) > 0) {
            $errors = $errorExtractor->extract($violations);

            return $this->respondWithError(
                'Validation error has been detected!',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $dkp = $request->get('digikalaDkp');

        if (!$this->isValidDkp($dkp)) {
            return $this->respondWithError(
                'Invalid dkp',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        if (!$product->hasDigikalaDkp()) {
            $product->setDigikalaDkp($dkp);
            $em->flush();
        }

        $bus->dispatch(new UpdateFromOutsource($product->getId()));

        return $this->setMessage('Request submitted successfully and will be processed soon.')->respond();
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="dkSellerId", type="string"),
     *         @OA\Property(property="seller", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Request submitted successfully",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/import/dk-seller-products", name: "import.dk_seller_products", methods: ["POST"])]
    public function importDkSellerProducts(
        Request $request,
        MessageBusInterface $bus,
    ): JsonResponse {
        $form = $this->createForm(ImportDkSellerProductsType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $dkSellerId = $form->get('dkSellerId')->getData();
        $seller     = $form->get('seller')->getData();

        if (!$this->isValidDkSeller($dkSellerId)) {
            return $this->respondWithError(
                'Invalid dk seller',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $bus->dispatch(new ImportDigikalaSellerProducts($dkSellerId, $seller->getId()));

        return $this->setMessage('Request submitted successfully and will be processed soon.')->respond();
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="digikalaDkp", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="seller", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Request submitted successfully",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/import/dk-products", name: "import.dk_products", methods: ["POST"])]
    public function importDkProducts(
        Request $request,
        MessageBusInterface $bus,
    ): JsonResponse {
        $form = $this->createForm(ImportDkProductsType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $DKPs = $form->get('digikalaDkp')->getData();
        $seller     = $form->get('seller')->getData();

        foreach ($DKPs as $DKP) {
            $bus->dispatch(new ImportProductFromDigikala($DKP, $seller->getId()));
        }

        return $this->setMessage('Request submitted successfully and will be processed soon.')->respond();
    }

    /**
     * @OA\Tag(name="Product")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="referencePrice", type="integer"),
     *         @OA\Property(property="priceTopMargin", type="integer"),
     *         @OA\Property(property="priceBottomMargin", type="integer")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Reference price updated successfully.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/reference-price", name: "update_reference_price", methods: ["POST"])]
    public function updateReferencePrice(
        Product $product,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $form = $this->createForm(ProductReferencePriceType::class, $product)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $em->flush();

        return $this->setMessage('Reference price updated successfully.')->respond();
    }

    private function isValidDkp(string $dkp): bool
    {
        return Response::HTTP_OK === $this->client->request('GET', DigikalaProductLink::generate($dkp))->getStatusCode();
    }

    private function isValidDkSeller(string $dkSellerId): bool
    {
        return Response::HTTP_OK === $this->client->request('GET', DigikalaSellerPageLink::generate($dkSellerId))->getStatusCode();
    }
}
