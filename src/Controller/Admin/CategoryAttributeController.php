<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CategoryAttribute;
use App\Form\Type\Admin\CategoryAttributeType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/category-attributes", name: "category.attribute.")]
class CategoryAttributeController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Category Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Return list of category attributes.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=CategoryAttribute::class, groups={"category.attribute.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(CategoryAttribute::class, $request->query->all()),
            context: ['groups' => ['category.attribute.read']]
        );
    }

    /**
     * @OA\Tag(name="Category Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Return a category attribute details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttribute::class, groups={"category.attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(CategoryAttribute $categoryAttribute): JsonResponse
    {
        return $this->respond($categoryAttribute, context: ['groups' => ['category.attribute.read']]);
    }

    /**
     * @OA\Tag(name="Category Attribute")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryAttributeType::class, groups={"category.attribute.store"})))
     * @OA\Response(
     *     response=201,
     *     description="Cateogry attribute successfully created, returns the newly created category attribute.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttribute::class, groups={"category.attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            CategoryAttributeType::class,
            options: [
                'validation_groups' => 'category.attribute.store',
            ]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $categoryAttribute = $form->getData();

            $this->manager->persist($categoryAttribute);
            $this->manager->flush();

            return $this->respond(
                $categoryAttribute,
                Response::HTTP_CREATED,
                context: ['groups' => 'category.attribute.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Attribute")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryAttributeType::class, groups={"category.attribute.update"})))
     * @OA\Response(
     *     response=200,
     *     description="Category attribute successfully updated, returns the newly updated category attribute.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttribute::class, groups={"category.attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, CategoryAttribute $categoryAttribute): JsonResponse
    {
        $form = $this->createForm(
            CategoryAttributeType::class,
            $categoryAttribute,
            [
                'validation_groups' => 'category.attribute.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($categoryAttribute);
            $this->manager->flush();

            return $this->respond($categoryAttribute, context: ['groups' => 'category.attribute.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Category attribute successfully deleted",
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
    public function delete(CategoryAttribute $categoryAttribute): JsonResponse
    {
        try {
            $categoryAttributeId = $categoryAttribute->getId();
            $this->manager->remove($categoryAttribute);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting category attribute!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($categoryAttributeId);
    }
}
