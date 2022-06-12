<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Category;
use App\Entity\CategoryDiscountRange;
use App\Entity\CategoryProductIdentifier;
use App\Entity\Media\CategoryImage;
use App\Form\CategoryType;
use App\Service\ORM\CustomFilters\Category\Admin\IsLeafCategoryCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/categories", name: "categories.")]
class CategoryController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Category")
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
     *            @OA\Items(ref=@Model(type=Category::class, groups={"categories.index", "media"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         IsLeafCategoryCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        $qb = $service->filter(Category::class, $request->query->all());

        $relations = [
            CategoryImage::class             => ['image', 'img', ['id', 'path', 'alt']],
            CategoryDiscountRange::class     => ['discountRange', 'cdr', ['id']],
            CategoryProductIdentifier::class => ['categoryProductIdentifier', 'cpi', ['id', 'required']],
            Category::class                  => ['parent', 'p', ['id']],
        ];

        foreach ($relations as $relation => [$field, $alternateAlias, $fields]) {
            $alias = $service::getJoinAlias(Category::class, $relation);

            if (null === $alias) {
                $alias = $alternateAlias;
                [$rootAlias] = $qb->getRootAliases();

                $qb->leftJoin("{$rootAlias}.{$field}", $alias)
                   ->addSelect(sprintf("PARTIAL %s.{%s}", $alias, implode(',', $fields)));
            }
        }

        unset($relations[Category::class]);

        foreach ($relations as $relation => [$field, $alternateAlias]) {
            $parentAlias = $service::getJoinAlias(Category::class, Category::class) ?? 'p';
            $alias       = "p_$alternateAlias";

            $qb->leftJoin("{$parentAlias}.{$field}", $alias)
               ->addSelect(sprintf("PARTIAL %s.{id}", $alias));
        }

        return $this->respondWithPagination(
            $qb->getQuery()->setHint(Query::HINT_REFRESH, true),
            context: ['groups' => ['categories.index', 'media']]
        );
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Return list of category options.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Category::class, groups={"category.product_options.index"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/options", name: "options.index", methods: ["GET"])]
    public function getCategoryOptions(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        $data = $request->query->all();

        $data['filter'] = $data['filter'] ?? [];

        $data['filter']['categoryProductOptions.id']['gt'] = 0;

        return $this->respondWithPagination(
            $service->filter(Category::class, $data),
            context: ['groups' => ['category.product_options.index']]
        );
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Return category options by category id.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Category::class, groups={"category.product_options.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}/options", name: "options.show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function getOptions(Category $category): JsonResponse
    {
        return $this->respond($category, context: ['groups' => ['category.product_options.show']]);
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="parent", type="integer"),
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="pageTitle", type="string"),
     *         @OA\Property(property="subtitle", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="code", type="string"),
     *         @OA\Property(property="level", type="integer"),
     *         @OA\Property(property="configurations", type="object"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Create a new Category",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Category::class, groups={"categories.store"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            CategoryType::class,
            options: ['validation_groups' => 'categories.store', 'method' => 'POST']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            if ($request->request->has('configurations')) {
                $category->setConfigurations($request->get('configurations'));
            } else {
                return $this->respondWithError(
                    'Validation error has been detected!',
                    ['configurations' => 'Value is required.'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $this->manager->persist($category);
            $this->manager->flush();

            return $this->respond(
                $category,
                Response::HTTP_CREATED,
                context: ['groups' => 'categories.store']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Return a Category details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Category::class, groups={"categories.show", "media"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Category $category): JsonResponse
    {
        return $this->respond($category, context: ['groups' => ['categories.show', 'media']]);
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Delete a Category.",
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
    #[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(Category $category): JsonResponse
    {
        if ($category->hasProducts()) {
            throw new UnauthorizedHttpException('', 'You cannot delete a category which has products');
        }

        $id = $category->getId();

        $this->manager->remove($category);
        $this->manager->flush();

        return $this->respondEntityRemoved($id);
    }

    /**
     * @OA\Tag(name="Category")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="parent", type="integer"),
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="pageTitle", type="string"),
     *         @OA\Property(property="subtitle", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="code", type="string"),
     *         @OA\Property(property="commission", type="number"),
     *         @OA\Property(property="maxLeadTime", type="number"),
     *         @OA\Property(property="level", type="integer"),
     *         @OA\Property(property="configurations", type="object"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update Category data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Category::class, groups={"categories.update", "media"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, Category $category): JsonResponse
    {
        $form = $this->createForm(
            CategoryType::class,
            $category,
            ['validation_groups' => 'categories.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        $maxLeadTime = $request->get('maxLeadTime');
        if (!isset($maxLeadTime)) {
            $form->get('maxLeadTime')->addError(new FormError('maxLeadTime is required'));
        }

        $commission = $request->get('commission');
        if (!isset($commission)) {
            $form->get('commission')->addError(new FormError('commission is required'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('configurations')) {
                $category->setConfigurations($request->get('configurations'));
            }

            $this->manager->persist($category);
            $this->manager->flush();

            return $this->respond($category, context: ['groups' => 'categories.update', 'media']);
        }

        return $this->respondValidatorFailed($form);
    }
}
