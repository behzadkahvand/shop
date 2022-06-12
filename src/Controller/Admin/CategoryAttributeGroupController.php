<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\CategoryAttributeGroup;
use App\Form\Type\Admin\CategoryAttributeGroupType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/category-attribute-groups", name: "category.attribute.group.")]
class CategoryAttributeGroupController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Category Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Return list of category attribute groups.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=CategoryAttributeGroup::class, groups={"category.attribute.group.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(CategoryAttributeGroup::class, $request->query->all()),
            context: ['groups' => ['category.attribute.group.read']]
        );
    }

    /**
     * @OA\Tag(name="Category Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Return a category attribute group details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttributeGroup::class, groups={"category.attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(CategoryAttributeGroup $categoryAttributeGroup): JsonResponse
    {
        return $this->respond($categoryAttributeGroup, context: ['groups' => ['category.attribute.group.read']]);
    }

    /**
     * @OA\Tag(name="Category Attribute Group")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryAttributeGroupType::class, groups={"category.attribute.group.store"})))
     * @OA\Response(
     *     response=201,
     *     description="Cateogry attribute group successfully created, returns the newly created category attribute group.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttributeGroup::class, groups={"category.attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            CategoryAttributeGroupType::class,
            options: [
                'validation_groups' => 'category.attribute.group.store',
            ]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $categoryAttributeGroup = $form->getData();

            $this->manager->persist($categoryAttributeGroup);
            $this->manager->flush();

            return $this->respond(
                $categoryAttributeGroup,
                Response::HTTP_CREATED,
                context: ['groups' => 'category.attribute.group.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Attribute Group")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CategoryAttributeGroupType::class, groups={"category.attribute.group.update"})))
     * @OA\Response(
     *     response=200,
     *     description="Category attribute group successfully updated, returns the newly updated category attribute group.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CategoryAttributeGroup::class, groups={"category.attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, CategoryAttributeGroup $categoryAttributeGroup): JsonResponse
    {
        $form = $this->createForm(
            CategoryAttributeGroupType::class,
            $categoryAttributeGroup,
            [
                'validation_groups' => 'category.attribute.group.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($categoryAttributeGroup);
            $this->manager->flush();

            return $this->respond($categoryAttributeGroup, context: ['groups' => 'category.attribute.group.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Category Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Category attribute group successfully deleted",
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
    public function delete(CategoryAttributeGroup $categoryAttributeGroup): JsonResponse
    {
        try {
            $categoryAttributeGroupId = $categoryAttributeGroup->getId();
            $this->manager->remove($categoryAttributeGroup);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting category attribute group!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($categoryAttributeGroupId);
    }
}
