<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Attribute;
use App\Form\Type\Admin\AttributeType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/attributes", name: "attribute.")]
class AttributeController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Return list of attributes",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Attribute::class, groups={"attribute.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(Attribute::class, $request->query->all()),
            context: ['groups' => ['attribute.read']]
        );
    }

    /**
     * @OA\Tag(name="Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Return a attribute details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Attribute::class, groups={"attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Attribute $attribute): JsonResponse
    {
        return $this->respond($attribute, context: ['groups' => ['attribute.read']]);
    }

    /**
     * @OA\Tag(name="Attribute")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="type", type="string"),
     *         @OA\Property(property="isMultiple", type="boolean"),
     *         @OA\Property(property="list", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Attribute successfully created, returns the newly created attribute.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Attribute::class, groups={"attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            AttributeType::class,
            options: ['validation_groups' => 'attribute.store',]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $attribute = $form->getData();

            $this->manager->persist($attribute);
            $this->manager->flush();

            return $this->respond(
                $attribute,
                Response::HTTP_CREATED,
                context: ['groups' => 'attribute.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="type", type="string"),
     *         @OA\Property(property="isMultiple", type="boolean"),
     *         @OA\Property(property="list", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Attribute successfully updated, returns the newly updated attribute.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Attribute::class, groups={"attribute.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        $form = $this->createForm(
            AttributeType::class,
            $attribute,
            [
                'validation_groups' => 'attribute.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($attribute);
            $this->manager->flush();

            return $this->respond($attribute, context: ['groups' => 'attribute.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute")
     * @OA\Response(
     *     response=200,
     *     description="Attribute successfully deleted",
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
    public function delete(Attribute $attribute): JsonResponse
    {
        if ($attribute->isAssignedToCategory() || $attribute->isAssignedToProduct()) {
            return $this->respondWithError(
                'You can not delete an attribute that is assigned to a product or category!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $attributeId = $attribute->getId();
            $this->manager->remove($attribute);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting attribute!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($attributeId);
    }
}
