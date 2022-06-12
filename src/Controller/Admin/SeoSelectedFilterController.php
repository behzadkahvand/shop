<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Seo\SeoSelectedFilter;
use App\Form\Type\Admin\Seo\SeoSelectedFilterType;
use App\Form\Type\Admin\Seo\UpdateSeoSelectedFilterType;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Seo\SeoSelectedFilter\AddSeoSelectedFilterService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/seo/selected-filters", name: "seo.selected_filters.")]
class SeoSelectedFilterController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Seo Selected Filter")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[category.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=category.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of seo selected filters",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=SeoSelectedFilter::class, groups={"seo.selected_filters.store.show"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respondWithPagination(
            $filterService->filter(SeoSelectedFilter::class, $request->query->all()),
            context: ['groups' => 'seo.selected_filters.store.show']
        );
    }

    /**
     * @OA\Tag(name="Seo Selected Filter")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="category", type="integer", description="category id"),
     *         @OA\Property(property="brand", type="integer", description="brand id"),
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="starred", type="boolean"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Create seo selected filter",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default="Seo selected filter is stored successfully."),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SeoSelectedFilter::class, groups={"seo.selected_filters.store"})
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
     * @OA\Response(
     *     response=400,
     *     description="Seo selected filter existence!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Seo selected filter is already exists!"),
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
    public function store(Request $request, AddSeoSelectedFilterService $addSeoSelectedFilter): JsonResponse
    {
        $form = $this->createForm(SeoSelectedFilterType::class, options: ['method' => 'POST'])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        try {
            $seoSelectedFilter = $addSeoSelectedFilter->perform($form->getData());
        } catch (UniqueConstraintViolationException $e) {
            return $this->respondWithError(
                'Seo selected filter is already exists!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->setMessage('Seo selected filter is stored successfully.')
                    ->respond($seoSelectedFilter, context: ['groups' => 'seo.selected_filters.store']);
    }

    /**
     * @OA\Tag(name="Seo Selected Filter")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="metaDescription", type="string"),
     *         @OA\Property(property="starred", type="boolean"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update seo selected filter",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Seo selected filter is updated successfully."),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SeoSelectedFilter::class, groups={"seo.selected_filters.store"})
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
    public function update(SeoSelectedFilter $seoSelectedFilter, Request $request): JsonResponse
    {
        $form = $this->createForm(UpdateSeoSelectedFilterType::class, $seoSelectedFilter)
                     ->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $this->manager->flush();

        return $this->setMessage('Seo selected filter is updated successfully.')
                    ->respond($seoSelectedFilter, context: ['groups' => 'seo.selected_filters.store']);
    }

    /**
     * @OA\Tag(name="Seo Selected Filter")
     * @OA\Response(
     *     response=200,
     *     description="Remove seo selected filter",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer", description="Removed seo selected filter id")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "remove", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function remove(SeoSelectedFilter $seoSelectedFilter): JsonResponse
    {
        try {
            $seoSelectedFilterId = $seoSelectedFilter->getId();

            $this->manager->remove($seoSelectedFilter);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is problem on removing seo selected filter!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($seoSelectedFilterId);
    }

    /**
     * @OA\Tag(name="Seo Selected Filter")
     * @OA\Response(
     *     response=200,
     *     description="Return a seo selected filter details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=SeoSelectedFilter::class, groups={"seo.selected_filters.store"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(SeoSelectedFilter $seoSelectedFilter): JsonResponse
    {
        return $this->respond($seoSelectedFilter, context: ['groups' => ['seo.selected_filters.store']]);
    }
}
