<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\AttributeList;
use App\Form\Type\Admin\AttributeListType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/attribute-lists", name: "attribute.list.")]
class AttributeListController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Attribute List")
     * @OA\Response(
     *     response=200,
     *     description="Return list of attribute lists",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=AttributeList::class, groups={"attribute.list.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(AttributeList::class, $request->query->all()),
            context: ['groups' => ['attribute.list.read']]
        );
    }

    /**
     * @OA\Tag(name="Attribute List")
     * @OA\Response(
     *     response=200,
     *     description="Return a attribute list details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeList::class, groups={"attribute.list.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(AttributeList $attributeList): JsonResponse
    {
        return $this->respond($attributeList, context: ['groups' => ['attribute.list.read']]);
    }

    /**
     * @OA\Tag(name="Attribute List")
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
     *     description="Attribute list successfully created, returns the newly created attribute list",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeList::class, groups={"attribute.list.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            AttributeListType::class,
            options: ['validation_groups' => 'attribute.list.store',]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $attributeList = $form->getData();

            $this->manager->persist($attributeList);
            $this->manager->flush();

            return $this->respond(
                $attributeList,
                Response::HTTP_CREATED,
                context: ['groups' => 'attribute.list.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute List")
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
     *     description="Attribute list successfully updated, returns the newly updated attribute list.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeList::class, groups={"attribute.list.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, AttributeList $attributeList): JsonResponse
    {
        $form = $this->createForm(
            AttributeListType::class,
            $attributeList,
            [
                'validation_groups' => 'attribute.list.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($attributeList);
            $this->manager->flush();

            return $this->respond($attributeList, context: ['groups' => 'attribute.list.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute List")
     * @OA\Response(
     *     response=200,
     *     description="Attribute list successfully deleted",
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
    public function delete(AttributeList $attributeList): JsonResponse
    {
        if ($attributeList->isAssignedToProducts()) {
            return $this->respondWithError(
                'You can not delete a list that is assigned to a product!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $attributeListId = $attributeList->getId();
            $this->manager->remove($attributeList);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting list!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($attributeListId);
    }
}
