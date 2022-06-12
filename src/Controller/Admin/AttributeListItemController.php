<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\AttributeListItem;
use App\Form\Type\Admin\AttributeListItemType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/attribute-list-items", name: "attribute.list.item.")]
class AttributeListItemController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Attribute List Item")
     * @OA\Response(
     *     response=200,
     *     description="Return list of attribute list Items",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=AttributeListItem::class, groups={"attribute.list.item.read"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(AttributeListItem::class, $request->query->all()),
            context: ['groups' => ['attribute.list.item.read']]
        );
    }

    /**
     * @OA\Tag(name="Attribute List Item")
     * @OA\Response(
     *     response=200,
     *     description="Return a attribute list item details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeListItem::class, groups={"attribute.list.item.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(AttributeListItem $attributeListItem): JsonResponse
    {
        return $this->respond($attributeListItem, context: ['groups' => ['attribute.list.item.read']]);
    }

    /**
     * @OA\Tag(name="Attribute List Item")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="list", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Attribute list item successfully created, returns the newly created attribute list item.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeListItem::class, groups={"attribute.list.item.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            AttributeListItemType::class,
            options: ['validation_groups' => 'attribute.list.item.store',]
        );

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $attributeListItem = $form->getData();

            $this->manager->persist($attributeListItem);
            $this->manager->flush();

            return $this->respond(
                $attributeListItem,
                Response::HTTP_CREATED,
                context: ['groups' => 'attribute.list.item.read']
            );
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute List Item")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="list", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Attribute list item successfully updated, returns the newly updated attribute list item.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=AttributeListItem::class, groups={"attribute.list.item.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, AttributeListItem $attributeListItem): JsonResponse
    {
        $form = $this->createForm(
            AttributeListItemType::class,
            $attributeListItem,
            [
                'validation_groups' => 'attribute.list.item.update',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $this->manager->persist($attributeListItem);
            $this->manager->flush();

            return $this->respond($attributeListItem, context: ['groups' => 'attribute.list.item.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Attribute List Item")
     * @OA\Response(
     *     response=200,
     *     description="Attribute list item successfully deleted",
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
    public function delete(AttributeListItem $attributeListItem): JsonResponse
    {
        if ($attributeListItem->isAssignedToProducts()) {
            return $this->respondWithError(
                'You can not delete a list item that is assigned to a product!',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $attributeListId = $attributeListItem->getId();
            $this->manager->remove($attributeListItem);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting list item!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($attributeListId);
    }
}
