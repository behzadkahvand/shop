<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\AttributeGroup;
use App\Form\Type\Admin\AttributeGroupType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/attribute-groups", name: "attribute.group.")]
class AttributeGroupController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Return list of attribute groups.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=AttributeGroup::class, groups={"attribute.group.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(AttributeGroup::class, $request->query->all()),
            context: ['groups' => ['attribute.group.read']]
        );
    }

    /**
     * @OA\Tag(name="Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Return a attribute group details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeGroup::class, groups={"attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(AttributeGroup $attributeGroup): JsonResponse
    {
        return $this->respond($attributeGroup, context: ['groups' => ['attribute.group.read']]);
    }

    /**
     * @OA\Tag(name="Attribute Group")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Attribute group successfully created, returns the newly created attribute group.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeGroup::class, groups={"attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            AttributeGroupType::class,
            options: ['validation_groups' => 'attribute.group.store',]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $attributeGroup = $form->getData();

            $this->manager->persist($attributeGroup);
            $this->manager->flush();

            return $this->respond(
                $attributeGroup,
                Response::HTTP_CREATED,
                context: ['groups' => 'attribute.group.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute Group")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Attribute group successfully updated, returns the newly updated attribute group.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeGroup::class, groups={"attribute.group.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, AttributeGroup $attributeGroup): JsonResponse
    {
        $form = $this->createForm(
            AttributeGroupType::class,
            $attributeGroup,
            [
                'validation_groups' => 'attribute.group.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($attributeGroup);
            $this->manager->flush();

            return $this->respond($attributeGroup, context: ['groups' => 'attribute.group.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute Group")
     * @OA\Response(
     *     response=200,
     *     description="Attribute group successfully deleted",
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
    public function delete(AttributeGroup $attributeGroup): JsonResponse
    {
        if ($attributeGroup->isAssignedToCategory() || $attributeGroup->isAssignedToTemplate()) {
            return $this->respondWithError(
                'You can not delete a group that is assigned to a category or template!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $attributeGroupId = $attributeGroup->getId();
            $this->manager->remove($attributeGroup);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting attribute group!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($attributeGroupId);
    }
}
