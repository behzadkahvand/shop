<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Notification;
use App\Form\NotificationType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/notifications", name: "notifications.")]
class NotificationController extends Controller
{
    /**
     * @OA\Tag(name="Notification")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[type]=SMS",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of notifications",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Notification::class, groups={"notification.index"}))
     *         ),
     *         @OA\Property(
     *             property="metas",
     *             type="object",
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="perPage", type="integer"),
     *             @OA\Property(property="totalItems", type="integer"),
     *             @OA\Property(property="sections", type="array", @OA\Items(type="string")),
     *             @OA\Property(
     *                 property="codes",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sectionCode",
     *                     type="array",
     *                    @OA\Items(
     *                        type="object",
     *                        @OA\Property(property="code", type="string"),
     *                        @OA\Property(property="variables", type="array", @OA\Items(type="string"))
     *                    )
     *                 )
     *             ),
     *         )
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(
        Request                   $request,
        QueryBuilderFilterService $filterService,
        array                     $editableNotifications
    ): JsonResponse {
        return $this->respondWithPagination(
            $filterService->filter(Notification::class, $request->query->all()),
            context: ['groups' => 'notification.index'],
            meta: [
                'sections' => array_keys($editableNotifications),
                'codes'    => $editableNotifications,
            ]
        );
    }

    /**
     * @OA\Tag(name="Notification")
     * @OA\Response(
     *     response=200,
     *     description="Notifications resource",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Notification::class, groups={"notification.show"})
     *         ),
     *         @OA\Property(
     *             property="metas",
     *             type="object",
     *             @OA\Property(
     *                 property="variables",
     *                 type="object",
     *                 @OA\Property(property="variableName", type="string")
     *             ),
     *         )
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Notification $notification, array $editableNotifications): JsonResponse
    {
        $info = collect($editableNotifications[$notification->getSection()])->first(
            fn(array $item) => $notification->getCode() === $item['code'],
            ['variables' => []]
        );

        return $this->setMetas(['variables' => $info['variables']])
                    ->respond($notification, context: ['groups' => ['notification.show'],]);
    }

    /**
     * @OA\Tag(name="Notification")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=NotificationType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Notification resource",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Notification::class, groups={"notification.show"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT"])]
    public function update(
        Request                $request,
        Notification           $notification,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $form = $this->createForm(NotificationType::class, $notification, [
            'method' => 'PUT',
        ])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $entityManager->flush();

        return $this->respond(
            $notification,
            context: ['groups' => ['notification.show'],]
        );
    }
}
